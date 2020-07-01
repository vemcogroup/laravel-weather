<?php

namespace Vemcogroup\Weather\Tests;

use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Exceptions\WeatherException;

class WeatherTest extends TestCase
{

    /**
     * @test
     */
    public function it_tests_no_api_key_exception(): void
    {
        config()->set('weather.api_key', null);

        $this->expectExceptionMessage(WeatherException::noApiKey()->getMessage());
        $this->expectExceptionCode(WeatherException::noApiKey()->getCode());

        weather()->getData(new Request('test address'));
    }

    /**
     * @test
     */
    public function it_tests_no_provider_exception(): void
    {
        config()->set('weather.provider', null);

        $this->expectExceptionMessage(WeatherException::noProvider()->getMessage());
        $this->expectExceptionCode(WeatherException::noProvider()->getCode());

        weather()->getData(new Request('test address'));
    }

    /**
     * @test
     */
    public function it_tests_wrong_provider_exception(): void
    {
        config()->set('weather.provider', 'wrong_provider');

        $this->expectExceptionMessage(WeatherException::wrongProvider()->getMessage());
        $this->expectExceptionCode(WeatherException::wrongProvider()->getCode());

        weather()->getData(new Request('test address'));
    }
}
