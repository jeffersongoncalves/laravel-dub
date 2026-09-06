<?php

use JeffersonGoncalves\Dub\DubServiceProvider;

it('registers the service provider', function () {
    expect(app()->getProviders(DubServiceProvider::class))->not->toBeEmpty();
});

it('merges the published config values', function () {
    expect(config('dub.api_key'))->toBe('fake-api-key');
    expect(config('dub.api_url'))->toBe('https://api.dub.co');
    expect(config('dub.timeout'))->toBe(5);
});

it('ships sane config defaults', function () {
    $config = require __DIR__.'/../../config/dub.php';

    expect($config)->toHaveKeys(['api_key', 'api_url', 'timeout']);
    expect($config['api_url'])->toBe('https://api.dub.co');
    expect($config['timeout'])->toBe(8);
});
