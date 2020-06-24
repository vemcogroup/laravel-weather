<?php

namespace Vemcogroup\Weather\Tests;

abstract class WeatherTest extends TestCase
{

    abstract public function itShouldReturnCorrectWeatherData(): void;
    abstract public function itShouldReturnCorrectForecastData(): void;

    protected function checkWeatherResponse($response): void
    {
        $this->assertArrayHasKey('2020-01-01 13:59', $response);

        $file['2020-01-01 13:59'] = json_decode($this->getFile('response.json'));
        $this->assertEquals($file, $response);

    }
}
