<?php

namespace Vemcogroup\Weather\Tests\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Response;
use Vemcogroup\Weather\Tests\TestCase;

abstract class ProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2020-01-01');
    }

    abstract public function itShouldReturnCorrectData(): void;
    abstract public function itShouldReturnCorrectDataForCurrentDay(): void;

    protected function checkWeatherResponse($responses): void
    {
        $this->assertArrayHasKey('2020-01-01 13:59', $responses);

        /** @var Response $response */
        foreach ($responses as $response) {
            $this->assertEquals(55.35, $response->getLatitude());
            $this->assertEquals(10.5, $response->getLongitude());
            $this->assertEquals(3, $response->getCurrently()->getTemperature()->getCurrent());
        }
    }
}
