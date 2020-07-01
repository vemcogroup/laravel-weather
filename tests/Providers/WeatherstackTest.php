<?php

namespace Vemcogroup\Weather\Tests\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Providers\Weatherstack;

class WeatherstackTest extends ProviderTest
{

    /**
     * @test
     */
    public function itShouldReturnCorrectHistoricalData(): void
    {
        $this->addMockHandler(200, $this->getFile('weatherstack/historical.json'));

        $request = (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
                ->atDates([Carbon::parse('2020-01-01 13:59'), Carbon::parse('2020-01-02 13:59')])
                ->withOption('units', 'si')
                ->withOption('lang', 'en')
                ->withOption('exclude', 'minutely,hourly,alerts,flags,daily')
                ->withTimezone('Europe/Copenhagen');

        $responses = (new Weatherstack)->getHistorical($request);
        $this->checkWeatherResponse($responses);
    }

    /**
     * @test
     */
    public function itShouldReturnCorrectForecastData(): void
    {
        $this->addMockHandler(200, $this->getFile('weatherstack/forecast.json'));

        $requests = (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
            ->withOption('units', 'si')
            ->withOption('lang', 'en');

        $responses = (new Weatherstack)->getForecast($requests);
        $this->checkWeatherResponse($responses);
    }
}
