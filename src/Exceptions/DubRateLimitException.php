<?php

namespace JeffersonGoncalves\Dub\Exceptions;

use RuntimeException;

/**
 * Raised when the Dub API answers a call with a 429 rate-limit response.
 * Callers can catch this and back off for `$retryAfter` seconds instead of
 * hammering a limit that won't clear until the window resets.
 */
class DubRateLimitException extends RuntimeException
{
    public function __construct(public readonly int $retryAfter)
    {
        parent::__construct("Dub API rate limit hit; retry in {$retryAfter}s");
    }
}
