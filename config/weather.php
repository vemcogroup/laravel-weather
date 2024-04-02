<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Here you define your API Key for weather provider.
    |
    */

    'api_key' => env('WEATHER_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Weather provider
    |--------------------------------------------------------------------------
    |
    | Here you define provider you want to get weather information from.
    |
    */

    'provider' => env('WEATHER_PROVIDER'),

    /*
    |--------------------------------------------------------------------------
    | Midday
    |--------------------------------------------------------------------------
    |
    | Here you define what time is midday.
    |
    */
    'midday' => [
        'hour' => '13',
        'minute' => '59',
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Here you define the provider-specific settings
    |
    */
    'providers' => [

        'weatherkit' => [

            /*
            |--------------------------------------------------------------------------
            | Private key
            |--------------------------------------------------------------------------
            |
            | Private key issued from your Apple Developer Account
            |
            */
            'private-key' => base64_decode(env('WEATHER_KIT_PRIVATE_KEY')),

            /*
            |--------------------------------------------------------------------------
            | Algorithm
            |--------------------------------------------------------------------------
            |
            | The algorithm with which to sign the token. Weatherkit only supports ES256.
            |
            */
            'alg' => env('WEATHER_KIT_ALGORITHM', 'ES256'),

            /*
            |--------------------------------------------------------------------------
            | Key ID
            |--------------------------------------------------------------------------
            |
            | Key identifier obtained from your Apple Developer Account
            |
            */
            'kid' => env('WEATHER_KIT_KID'),

            /*
            |--------------------------------------------------------------------------
            | ID
            |--------------------------------------------------------------------------
            |
            | An identifier that consists of your 10-character Team ID and Service ID, separated by a period.
            |
            */
            'id' => env('WEATHER_KIT_ID'),

            /*
            |--------------------------------------------------------------------------
            | Issuer Claim Key
            |--------------------------------------------------------------------------
            |
            | The issuer claim key. This value is your 10-character Team ID from your developer account.
            |
            */
            'iss' => env('WEATHER_KIT_ISS'),

            /*
            |--------------------------------------------------------------------------
            | Subject Public Claim Key
            |--------------------------------------------------------------------------
            |
            | The subject public claim key. This value is your registered Service ID.
            |
            */
            'sub' => env('WEATHER_KIT_SUB'),
        ],

        'weatherstack' => [

            /*
            |--------------------------------------------------------------------------
            | Intervals
            |--------------------------------------------------------------------------
            |
            | Here you define the intervals for forecast and historical data.
            |
            */
            'intervals' => [
                'forecast' => env('WEATHER_FORECAST_INTERVAL', 24),
                'historical' => env('WEATHER_HISTORICAL_INTERVAL', 1),
            ],
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Historical Date status
    |--------------------------------------------------------------------------
    |
    | Here you define the Historical Date will add or not.
    |
    |
    */
    'historical_date_status' => env('WEATHER_HISTORICAL_DATE_STATUS', true),
    
    /*
    |--------------------------------------------------------------------------
    | Formated Response
    |--------------------------------------------------------------------------
    |
    | Here you can define returned response is formatted or default.
    |
    |
    */
    'formated_response' => [
        'historical' => env('WEATHER_HISTORICAL_FORMATED_RESPONSE', true),
    ],
];
