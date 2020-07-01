<?php

namespace Vemcogroup\Weather\Tests\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Response;
use Vemcogroup\Weather\Tests\TestCase;

abstract class ProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2020-01-01');
    }

    abstract public function itShouldReturnCorrectForecastData(): void;
    abstract public function itShouldReturnCorrectHistoricalData(): void;

    protected function checkWeatherResponse($responses): void
    {
        $this->assertArrayHasKey('2020-01-01 13:59', $responses);

        /** @var Response $response */
        foreach ($responses as $response) {
            $this->assertEquals(55.5779099, $response->getLatitude());
            $this->assertEquals(9.6559581, $response->getLongitude());
            $this->assertEquals(18.83, $response->getCurrently()->getTemperature()->getCurrent());
        }
    }
}
