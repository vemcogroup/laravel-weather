<?php

use Vemcogroup\Weather\WeatherProvider;

if (!function_exists('weather')) {
    function weather(): WeatherProvider
    {
        return app(WeatherProvider::class);
    }
}