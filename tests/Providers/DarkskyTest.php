<?php

namespace Vemcogroup\Weather\Tests\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Tests\WeatherTest;
use Vemcogroup\Weather\Providers\Darksky;

class DarkskyTest extends WeatherTest
{

    /**
     * @test
     */
    public function itShouldReturnCorrectWeatherData(): void
    {
        $this->addMockHandler(200, $this->getFile('geocoder.json'));
        $this->addMockHandler(200, $this->getFile('darksky/timemachine_response_1.json'));
        $this->addMockHandler(200, $this->getFile('darksky/timemachine_response_2.json'));

        $requests = [
            (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
                ->atDates([Carbon::parse('2020-01-01 13:59'), Carbon::parse('2020-01-02 13:59')])
                ->withOption('units', 'si')
                ->withOption('lang', 'en')
                ->withOption('exclude', 'minutely,hourly,alerts,flags,daily')
                ->withTimezone('Europe/Copenhagen'),
        ];

        $response = (new Darksky)->getWeather($requests);
        $this->checkWeatherResponse($response);
    }

    /**
     * @test
     */
    public function itShouldReturnCorrectForecastData(): void
    {
        $this->addMockHandler(200, $this->getFile('geocoder.json'));
        $this->addMockHandler(200, $this->getFile('darksky/forecast_response.json'));

        $request = (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
            ->withOption('units', 'si')
            ->withOption('lang', 'en');

        $forecast = (new Darksky)->getForecast($request);

        $this->assertEquals(55.3632242, $forecast->getLatitude());
        $this->assertEquals(10.4896986, $forecast->getLongitude());
        $this->assertEquals(21, $forecast->getCurrently()->getTemperature()->getCurrent());
//        $this->assertCount(7, $forecast->getDaily()->getData());
//        $this->assertEquals('partly-cloudy-day', $forecast->getDaily()->getData()[0]->getIcon());
    }
}
