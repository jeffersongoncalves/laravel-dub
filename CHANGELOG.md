# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/jeffersongoncalves/laravel-dub/commits/master/compare/1.0.0...main)

### Added

- Initial release.
- `DubClient` REST wrapper for the Dub.co API v1.
- Link management: `createLink()`, `listLinks()`, `getLink()`, `updateLink()`, `deleteLink()`, `bulkCreateLinks()`.
- Analytics: `analytics()`, `analyticsByCountry()`, `analyticsByDevice()`.
- Built-in rate-limit detection that throws `DubRateLimitException` on a 429, honouring the `Retry-After` header.
- Configurable API key, base URL, and timeout via `config/dub.php`.

## [1.0.0](https://github.com/jeffersongoncalves/laravel-dub/commits/master/compare/master...1.0.0) - 2026-09-06

Initial release.

- Static Dub.co API client: createLink, listLinks, getLink, updateLink, deleteLink, bulkCreateLinks
- Analytics: analytics, analyticsByCountry, analyticsByDevice
- Rate-limit aware: throws DubRateLimitException on 429, honouring Retry-After
- Config-driven (DUB_API_KEY, DUB_API_URL, DUB_TIMEOUT)
