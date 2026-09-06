<?php

namespace JeffersonGoncalves\Dub;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JeffersonGoncalves\Dub\Exceptions\DubRateLimitException;
use Throwable;

/**
 * Dub.co REST API v1 client. Wraps link management (create, list, get,
 * update, delete, bulk-create) and analytics (overview, by country, by
 * device) behind a small static client, threading the API key and
 * centralising rate-limit detection so callers get a thrown
 * DubRateLimitException rather than a silent failure during a limit window.
 */
class DubClient
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public static function createLink(array $data): ?array
    {
        return self::json(self::request('post', '/links', $data));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>|null
     */
    public static function listLinks(array $filters = []): ?array
    {
        return self::json(self::request('get', '/links', $filters));
    }

    /**
     * @param  array<string, mixed>  $filters  domain/key or linkId/externalId
     * @return array<string, mixed>|null
     */
    public static function getLink(array $filters): ?array
    {
        return self::json(self::request('get', '/links/info', $filters));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public static function updateLink(string $id, array $data): ?array
    {
        return self::json(self::request('patch', "/links/{$id}", $data));
    }

    public static function deleteLink(string $id): bool
    {
        $response = self::request('delete', "/links/{$id}");

        return $response !== null && $response->successful();
    }

    /**
     * @param  list<array<string, mixed>>  $links
     * @return array<string, mixed>|null
     */
    public static function bulkCreateLinks(array $links): ?array
    {
        return self::json(self::request('post', '/links/bulk', $links));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>|null
     */
    public static function analytics(array $filters = []): ?array
    {
        return self::json(self::request('get', '/analytics', $filters));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>|null
     */
    public static function analyticsByCountry(array $filters = []): ?array
    {
        return self::json(self::request('get', '/analytics/country', $filters));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>|null
     */
    public static function analyticsByDevice(array $filters = []): ?array
    {
        return self::json(self::request('get', '/analytics/device', $filters));
    }

    /**
     * Shared request/response handling: builds the authenticated request,
     * swallows transport failures into a logged null, and lets a 429 throw
     * outside the catch so it propagates to the caller.
     *
     * @param  'get'|'post'|'patch'|'delete'  $method
     * @param  array<int|string, mixed>  $params
     *
     * @throws DubRateLimitException when the Dub API answers with a 429
     */
    private static function request(string $method, string $path, array $params = []): ?Response
    {
        $url = self::baseUrl().$path;

        try {
            $request = Http::timeout(self::timeout())->withToken(self::apiKey());

            $response = match ($method) {
                'get' => $request->get($url, $params),
                'post' => $request->post($url, $params),
                'patch' => $request->patch($url, $params),
                'delete' => $request->delete($url, $params),
            };
        } catch (Throwable $e) {
            self::logFailure($method, $url, $e);

            return null;
        }

        // Rate-limit detection runs outside the catch above so the thrown
        // exception propagates to the caller instead of being swallowed and
        // logged as a generic fetch failure.
        self::throwIfRateLimited($response);

        return $response;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function json(?Response $response): ?array
    {
        if ($response === null || ! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return is_array($data) ? $data : null;
    }

    /**
     * Raise DubRateLimitException on a 429, honouring the `Retry-After`
     * header when present and falling back to a sane default otherwise.
     *
     * @throws DubRateLimitException
     */
    private static function throwIfRateLimited(Response $response): void
    {
        if ($response->status() !== 429) {
            return;
        }

        $retryAfterHeader = $response->header('Retry-After');

        $retryAfter = $retryAfterHeader !== '' ? (int) $retryAfterHeader : 60;

        throw new DubRateLimitException($retryAfter);
    }

    /**
     * Log an outbound-fetch exception with enough context to tell a timeout /
     * DNS / TLS failure apart from a clean non-2xx response (those return
     * their own sentinel without throwing).
     */
    private static function logFailure(string $context, string $target, Throwable $e): void
    {
        Log::warning('DubClient outbound fetch failed', [
            'context' => $context,
            'target' => $target,
            'exception' => $e::class,
            'message' => $e->getMessage(),
        ]);
    }

    private static function apiKey(): string
    {
        $apiKey = config('dub.api_key');

        return is_string($apiKey) ? $apiKey : '';
    }

    private static function baseUrl(): string
    {
        $apiUrl = config('dub.api_url');

        return is_string($apiUrl) && $apiUrl !== '' ? rtrim($apiUrl, '/') : 'https://api.dub.co';
    }

    private static function timeout(): int
    {
        return (int) config('dub.timeout', 8);
    }
}
