<?php

namespace Vemcogroup\Weather\Tests\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Providers\Darksky;

class DarkskyTest extends ProviderTest
{

    /**
     * @test
     */
    public function itShouldReturnCorrectHistoricalData(): void
    {
        $this->addMockHandler(200, $this->getFile('geocoder.json'));
        $this->addMockHandler(200, $this->getFile('darksky/historical_1.json'));
        $this->addMockHandler(200, $this->getFile('darksky/historical_2.json'));

        $request = (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
                ->atDates([Carbon::parse('2020-01-01 13:59'), Carbon::parse('2020-01-02 13:59')])
                ->withOption('units', 'si')
                ->withOption('lang', 'en')
                ->withOption('exclude', 'minutely,hourly,alerts,flags,daily')
                ->withTimezone('Europe/Copenhagen');

        $responses = (new Darksky)->getHistorical($request);
        $this->checkWeatherResponse($responses);
    }

    /**
     * @test
     */
    public function itShouldReturnCorrectForecastData(): void
    {
        $this->addMockHandler(200, $this->getFile('geocoder.json'));
        $this->addMockHandler(200, $this->getFile('darksky/forecast.json'));

        $request = (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
            ->withOption('units', 'si')
            ->withOption('lang', 'en');

        $responses = (new Darksky)->getForecast($request);
        $this->checkWeatherResponse($responses);
    }
}
