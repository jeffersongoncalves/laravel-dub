<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JeffersonGoncalves\Dub\DubClient;
use JeffersonGoncalves\Dub\Exceptions\DubRateLimitException;

it('creates a link', function () {
    Http::fake([
        'api.dub.co/links' => Http::response(['id' => 'link_1', 'url' => 'https://example.com'], 200),
    ]);

    expect(DubClient::createLink(['url' => 'https://example.com']))
        ->toBe(['id' => 'link_1', 'url' => 'https://example.com']);

    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && $request->url() === 'https://api.dub.co/links');
});

it('returns null when creating a link fails', function () {
    Http::fake([
        'api.dub.co/links' => Http::response(['error' => ['message' => 'Invalid URL']], 422),
    ]);

    expect(DubClient::createLink(['url' => 'not-a-url']))->toBeNull();
});

it('lists links', function () {
    Http::fake([
        'api.dub.co/links*' => Http::response([['id' => 'link_1'], ['id' => 'link_2']], 200),
    ]);

    expect(DubClient::listLinks(['domain' => 'dub.sh']))
        ->toBe([['id' => 'link_1'], ['id' => 'link_2']]);
});

it('returns null when listing links fails', function () {
    Http::fake([
        'api.dub.co/links*' => Http::response('', 500),
    ]);

    expect(DubClient::listLinks())->toBeNull();
});

it('gets a link by filters', function () {
    Http::fake([
        'api.dub.co/links/info*' => Http::response(['id' => 'link_1', 'key' => 'abc'], 200),
    ]);

    expect(DubClient::getLink(['domain' => 'dub.sh', 'key' => 'abc']))
        ->toBe(['id' => 'link_1', 'key' => 'abc']);
});

it('returns null when getting a link fails', function () {
    Http::fake([
        'api.dub.co/links/info*' => Http::response('', 404),
    ]);

    expect(DubClient::getLink(['key' => 'missing']))->toBeNull();
});

it('updates a link', function () {
    Http::fake([
        'api.dub.co/links/link_1' => Http::response(['id' => 'link_1', 'url' => 'https://updated.com'], 200),
    ]);

    expect(DubClient::updateLink('link_1', ['url' => 'https://updated.com']))
        ->toBe(['id' => 'link_1', 'url' => 'https://updated.com']);

    Http::assertSent(fn (Request $request) => $request->method() === 'PATCH');
});

it('returns null when updating a link fails', function () {
    Http::fake([
        'api.dub.co/links/link_1' => Http::response('', 404),
    ]);

    expect(DubClient::updateLink('link_1', ['url' => 'https://updated.com']))->toBeNull();
});

it('deletes a link', function () {
    Http::fake([
        'api.dub.co/links/link_1' => Http::response(['id' => 'link_1'], 200),
    ]);

    expect(DubClient::deleteLink('link_1'))->toBeTrue();

    Http::assertSent(fn (Request $request) => $request->method() === 'DELETE');
});

it('returns false when deleting a link fails', function () {
    Http::fake([
        'api.dub.co/links/link_1' => Http::response('', 404),
    ]);

    expect(DubClient::deleteLink('link_1'))->toBeFalse();
});

it('bulk creates links', function () {
    Http::fake([
        'api.dub.co/links/bulk' => Http::response([['id' => 'link_1'], ['id' => 'link_2']], 200),
    ]);

    expect(DubClient::bulkCreateLinks([['url' => 'https://a.com'], ['url' => 'https://b.com']]))
        ->toBe([['id' => 'link_1'], ['id' => 'link_2']]);
});

it('returns null when bulk creating links fails', function () {
    Http::fake([
        'api.dub.co/links/bulk' => Http::response('', 422),
    ]);

    expect(DubClient::bulkCreateLinks([['url' => 'https://a.com']]))->toBeNull();
});

it('fetches analytics', function () {
    Http::fake([
        'api.dub.co/analytics*' => Http::response(['clicks' => 42], 200),
    ]);

    expect(DubClient::analytics(['domain' => 'dub.sh']))->toBe(['clicks' => 42]);
});

it('returns null when analytics fails', function () {
    Http::fake([
        'api.dub.co/analytics*' => Http::response('', 500),
    ]);

    expect(DubClient::analytics())->toBeNull();
});

it('fetches analytics by country', function () {
    Http::fake([
        'api.dub.co/analytics/country*' => Http::response([['country' => 'US', 'clicks' => 10]], 200),
    ]);

    expect(DubClient::analyticsByCountry(['domain' => 'dub.sh']))
        ->toBe([['country' => 'US', 'clicks' => 10]]);
});

it('returns null when analytics by country fails', function () {
    Http::fake([
        'api.dub.co/analytics/country*' => Http::response('', 500),
    ]);

    expect(DubClient::analyticsByCountry())->toBeNull();
});

it('fetches analytics by device', function () {
    Http::fake([
        'api.dub.co/analytics/device*' => Http::response([['device' => 'Desktop', 'clicks' => 5]], 200),
    ]);

    expect(DubClient::analyticsByDevice(['domain' => 'dub.sh']))
        ->toBe([['device' => 'Desktop', 'clicks' => 5]]);
});

it('returns null when analytics by device fails', function () {
    Http::fake([
        'api.dub.co/analytics/device*' => Http::response('', 500),
    ]);

    expect(DubClient::analyticsByDevice())->toBeNull();
});

it('sends the bearer token on every request', function () {
    Http::fake([
        'api.dub.co/*' => Http::response(['ok' => true], 200),
    ]);

    DubClient::listLinks();

    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer fake-api-key'));
});

it('throws on a 429 honouring the Retry-After header', function () {
    Http::fake([
        'api.dub.co/links*' => Http::response('', 429, ['Retry-After' => '30']),
    ]);

    try {
        DubClient::listLinks();
        test()->fail('Expected DubRateLimitException to be thrown.');
    } catch (DubRateLimitException $e) {
        expect($e->retryAfter)->toBe(30);
    }
});

it('defaults the retry-after to 60 seconds when the header is missing', function () {
    Http::fake([
        'api.dub.co/links*' => Http::response('', 429),
    ]);

    expect(fn () => DubClient::listLinks())
        ->toThrow(DubRateLimitException::class);

    try {
        DubClient::listLinks();
    } catch (DubRateLimitException $e) {
        expect($e->retryAfter)->toBe(60);
    }
});

it('returns null and logs when the request throws', function () {
    Log::spy();

    Http::fake(fn () => throw new ConnectionException('Connection timed out'));

    expect(DubClient::listLinks())->toBeNull();

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context) => $message === 'DubClient outbound fetch failed'
            && $context['context'] === 'get')
        ->once();
});
