<?php

namespace Vemcogroup\Weather;

use Vemcogroup\Weather\Response;
use Illuminate\Support\Collection;
use Vemcogroup\Weather\Providers\Darksky;
use Vemcogroup\Weather\Providers\Provider;
use Vemcogroup\Weather\Providers\Weatherstack;
use Vemcogroup\Weather\Exceptions\WeatherException;

class WeatherProvider
{
    /**
     * @var Provider $provider
     */
    private $provider;
    private static $providers = [
        'darksky' => Darksky::class,
        'weatherstack' => Weatherstack::class,
    ];

    public function __construct()
    {
        if (!$name = config('weather.provider')) {
            throw WeatherException::noProvider();
        }

        if (!isset(self::$providers[$name])) {
            throw WeatherException::wrongProvider();
        }

        $this->provider = new self::$providers[$name]();
    }

    public function getForecast($requests): Collection
    {
        return $this->provider->getForecast($requests);
    }

    public function getHistorical($requests): Collection
    {
        return $this->provider->getHistorical($requests);
    }
}
