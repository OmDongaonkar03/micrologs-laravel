<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Micrologs Server URL
    |--------------------------------------------------------------------------
    |
    | The URL of the server where you installed Micrologs.
    | No trailing slash.
    |
    | Example: https://analytics.yourdomain.com
    |
    */

    'host' => env('MICROLOGS_HOST', ''),

    /*
    |--------------------------------------------------------------------------
    | Secret Key
    |--------------------------------------------------------------------------
    |
    | Your project secret key. Use X-API-Key auth.
    | Never use the public key here — that is for the JS snippet only.
    | Never commit this to version control — set it in your .env file.
    |
    */

    'key' => env('MICROLOGS_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | How long to wait (in seconds) for the Micrologs server to respond.
    | If the server is unreachable within this time, the SDK returns null
    | silently. Analytics failures should never affect your users.
    |
    */

    'timeout' => env('MICROLOGS_TIMEOUT', 5),

];

?>