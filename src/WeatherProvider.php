<?php

namespace Vemcogroup\Weather;

use Vemcogroup\Weather\Response;
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

    public function getData($requests): array
    {
        return $this->provider->getData($requests);
    }

    public function getForecast(Request $request): Response
    {
        return $this->provider->getForecast($request);
    }
}
