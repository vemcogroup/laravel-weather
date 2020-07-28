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

];
```

## Usage

At the moment this package support the folllowing weather services:

| Service | Website | Geocoding | Remarks |
| :--- | :--- | :---: | :--- |
| Dark Sky | https://darksky.net | Manual | Deprecated, not able to aquire api key https://blog.darksky.net |
| Weatherstack | https://weatherstack.com | Auto | For historical data a minimum Standard license is required. For forecast data a minimum Professional license is required.  |

For other weather services fill free to create an issue or make a Pull Request.

For `Manual` geocoding you need a Google geocode api key.  
Aquire it here https://developers.google.com/maps/documentation/geocoding/start and insert it into your .env file

```php
GOOGLE_MAPS_GEOCODING_API_KEY= 
```

### Request

Start by setting up your request

```php
$request = (new Vemcogroup\Weather\Request('1 Infinite Loop, Cupertino, CA 95014, USA'));
```

*Units*  
There two available unit types, default is Metric:

Metric (m): `Vemcogroup\Weather\Providers\Provider::WEATHER_UNITS_METRIC`  
Fahrenheit (f): `Vemcogroup\Weather\Providers\Provider::WEATHER_UNITS_FAHRENHEIT`

To change the response units you can do the following:

```php
$request->withUnits(Vemcogroup\Weather\Providers\Provider::WEATHER_UNITS_FAHRENHEIT);
```

*Locale*  
To change the locale for descriptions, summaries and other texts in the response, do the following:
```php
$request->withLocale('nl');
```
Locale need to be an 2-letter ISO Code of your preferred language.

*Dates*
If you need to select the dates to get weather data for E.g for historical data, set the dates like this:

```php
$request->withDates([$date, ...]);
```
All dates in the array need to `Carbon` objects.

*Options*  
If you need to set any extra options based in your selected weather provider you can do the following:

```php
$request->withOption('name', 'value');
```

### Current weather and forecast

To get current weather and forecast response you can do this:

```php
$weather = weather()->getForecast($request);
```

Weather response will always be a `Collection` of responses.  
Forecast days depends on weather service provider.

To get current weather data:

```php
$weather->first()->getCurrently(); // DataPoint
```

To get forecast you can take first element of response and get the forecast like this:

```php
$weather->first()->getDaily()->getData(); // array
```
Afterward run through the array with represent each day of the forecast on a `DataPoint` object.

### Historical

To get historical data you can do this:

```php
$weather = weather()->getHistorical($request);
```

Remember to set data on request.  
Response will be a collection with keys representing the dates for historical data.

### Response
To see what response data is available look into source code `/src/Objects`

