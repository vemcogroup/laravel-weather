# Laravel Weather

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vemcogroup/laravel-weather.svg?style=flat-square)](https://packagist.org/packages/vemcogroup/laravel-weather)
[![Total Downloads](https://img.shields.io/packagist/dt/vemcogroup/laravel-weather.svg?style=flat-square)](https://packagist.org/packages/vemcogroup/laravel-weather)

## Description

This package allows you to fetch weather data from different weather providers


## Installation

You can install the package via composer:

```bash
composer require vemcogroup/laravel-weather
```

The package will automatically register its service provider.

To publish the config file to `config/weather.php` run:

```bash
php artisan vendor:publish --provider="Vemcogroup\Weather\WeatherServiceProvider"
```

This is the default contents of the configuration:

```php
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
];
```

## Usage

TBD