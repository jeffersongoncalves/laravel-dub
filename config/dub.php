<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dub API Key
    |--------------------------------------------------------------------------
    |
    | The secret API key used to authenticate requests against the Dub API.
    | Create one at: https://app.dub.co/settings/tokens
    |
    */
    'api_key' => env('DUB_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Dub API URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Dub API. Override only if you're proxying requests
    | or targeting a different environment.
    |
    */
    'api_url' => env('DUB_API_URL', 'https://api.dub.co'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds to wait for a response before giving up.
    |
    */
    'timeout' => (int) env('DUB_TIMEOUT', 8),
];
