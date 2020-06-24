<?php

namespace Vemcogroup\Weather\Tests\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Tests\WeatherTest;
use Vemcogroup\Weather\Providers\Weatherstack;

class WeatherstackTest extends WeatherTest
{

    /**
     * @test
     */
    public function itShouldReturnCorrectWeatherData(): void
    {
        $this->addMockHandler(200, $this->getFile('geocoder.json'));
        $this->addMockHandler(200, $this->getFile('weatherstack/historical_response.json'));

        $requests = [
            (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
                ->atDate(Carbon::parse('2020-01-01 13:59'))
                ->withOption('units', 'si')
                ->withOption('lang', 'en')
                ->withOption('exclude', 'minutely,hourly,alerts,flags,daily')
                ->withTimezone('Europe/Copenhagen')
                ->withKey('2020-01-01 13:59'),
         ];

        $response = (new Weatherstack)->getWeather($requests);
        $this->checkWeatherResponse($response);
    }

    /**
     * @test
     */
    public function itShouldReturnCorrectForecastData(): void
    {
        $this->addMockHandler(200, $this->getFile('geocoder.json'));
        $this->addMockHandler(200, $this->getFile('weatherstack/forecast_response.json'));

        $geocode['lat'] = 55.3632242;
        $geocode['lng'] = 10.4896986;
        $parameters['units'] = 'si';
        $parameters['lang'] = 'en';

        $request = (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
            ->withOption('units', 'si')
            ->withOption('lang', 'en');

        $forecast = (new Weatherstack)->getForecast($request);

        $this->assertEquals('55.350', $forecast->getLatitude());
        $this->assertEquals('10.500', $forecast->getLongitude());
        $this->assertEquals(21, $forecast->getCurrently()->getTemperature()->getCurrent());
        $this->assertCount(7, $forecast->getDaily()->getData());
        $this->assertEquals('partly-cloudy-day', $forecast->getDaily()->getData()[0]->getIcon());
    }
}
