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
    | Intervals
    |--------------------------------------------------------------------------
    |
    | Here you define the intervals for forecast and historical data.
    | Only available for Weatherstack:
    |
    */
    'intervals' => [
        'forecast' => env('WEATHER_FORECAST_INTERVAL', 24),
        'historical' => env('WEATHER_HISTORICAL_INTERVAL', 1),
    ],
];
