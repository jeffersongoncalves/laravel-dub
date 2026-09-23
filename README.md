<div class="filament-hidden">

![Laravel Dub](https://raw.githubusercontent.com/jeffersongoncalves/laravel-dub/master/art/jeffersongoncalves-laravel-dub.png)

</div>

# Laravel Dub

[![Tests](https://github.com/jeffersongoncalves/laravel-dub/actions/workflows/tests.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-dub/actions/workflows/tests.yml)
[![PHPStan](https://github.com/jeffersongoncalves/laravel-dub/actions/workflows/phpstan.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-dub/actions/workflows/phpstan.yml)
[![Code Style](https://github.com/jeffersongoncalves/laravel-dub/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/jeffersongoncalves/laravel-dub/actions/workflows/fix-php-code-style-issues.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-dub.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-dub)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-dub.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-dub)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-dub.svg?style=flat-square)](LICENSE.md)

A lightweight [Dub.co](https://dub.co) API client for Laravel. It wraps the `api.dub.co` REST calls behind a small static client, threads your API key, and centralises rate-limit detection so callers get a thrown `DubRateLimitException` rather than a silent failure during a limit window.

## Features

- **Link management** — `createLink()`, `listLinks()`, `getLink()`, `updateLink()`, `deleteLink()`, `bulkCreateLinks()`
- **Analytics** — `analytics()`, `analyticsByCountry()`, `analyticsByDevice()`
- **Rate-limit aware** — throws `DubRateLimitException` on a `429`, honouring the `Retry-After` header, carrying the `retryAfter` seconds
- **No exceptions on plain non-2xx** — every method returns `null`/`false` on ordinary failures so callers don't need to wrap every call in a try/catch

## Installation

```bash
composer require jeffersongoncalves/laravel-dub
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="dub-config"
```

## Configuration

Add to your `.env`:

```env
DUB_API_KEY=dub_xxxxxxxxxxxxxxxxxxxx
```

Create an [API key](https://app.dub.co/settings/tokens) from your Dub workspace settings.

### Config Options

```php
// config/dub.php
return [
    'api_key' => env('DUB_API_KEY'),
    'api_url' => env('DUB_API_URL', 'https://api.dub.co'),
    'timeout' => (int) env('DUB_TIMEOUT', 8),
];
```

## Usage

```php
use JeffersonGoncalves\Dub\DubClient;
use JeffersonGoncalves\Dub\Exceptions\DubRateLimitException;

// Create a link
$link = DubClient::createLink([
    'url' => 'https://example.com',
    'domain' => 'dub.sh',
]);

// List links
$links = DubClient::listLinks(['domain' => 'dub.sh']);

// Get a link by domain/key or linkId/externalId
$link = DubClient::getLink(['domain' => 'dub.sh', 'key' => 'abc123']);

// Update a link
$link = DubClient::updateLink('link_1234', ['url' => 'https://example.com/updated']);

// Delete a link
$deleted = DubClient::deleteLink('link_1234');

// Bulk create links
$links = DubClient::bulkCreateLinks([
    ['url' => 'https://example.com/a'],
    ['url' => 'https://example.com/b'],
]);

// Analytics
$analytics = DubClient::analytics(['domain' => 'dub.sh', 'key' => 'abc123']);
$byCountry = DubClient::analyticsByCountry(['domain' => 'dub.sh', 'key' => 'abc123']);
$byDevice = DubClient::analyticsByDevice(['domain' => 'dub.sh', 'key' => 'abc123']);
```

### Handling rate limits

```php
try {
    $links = DubClient::listLinks();
} catch (DubRateLimitException $e) {
    // Back off for $e->retryAfter seconds (queue jobs can release($e->retryAfter)).
}
```

## Testing

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Formatting

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
