<?php

namespace Vemcogroup\Weather\Tests\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Providers\Provider;
use Vemcogroup\Weather\Providers\WeatherKit;
use Vemcogroup\Weather\Exceptions\WeatherException;
use function weather;

class WeatherKitTest extends ProviderTest
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('weather.provider', 'weatherkit');

        config()->set('weather.providers.weatherkit.private-key', '-----BEGIN EC PRIVATE KEY-----
            MHcCAQEEIMDqs1qze4ofGHHyujZ+uM0s2E+cr6koXxp47bxtl+OPoAoGCCqGSM49
            AwEHoUQDQgAE33lvC4vUBfP+7zbx5UAudqCXn9BhfWt2f/ahx5mEXo7VqioMDyrF
            kUZyuYq8fgO4yhgEEquJFyXPe5DYJ9hxiQ==
-----END EC PRIVATE KEY-----'
        );

        config()->set('weather.providers.weatherkit.alg', 'ES256');
        config()->set('weather.providers.weatherkit.kid', 'kid');
        config()->set('weather.providers.weatherkit.id', 'id');

        config()->set('weather.providers.weatherkit.iss', 'iss');
        config()->set('weather.providers.weatherkit.sub', 'sub');
    }


    /**
     * @test
     */
    public function itCheckIfConfigurationIsValid(): void
    {
        config()->set('weather.providers.weatherkit.private-key', 'not-a-private-key');

        $this->expectExceptionMessage('Missing or misconfigured WeatherKit settings, please add/edit it in .env');
        $this->expectExceptionCode(WeatherException::noApiKey()->getCode());

        weather()->getForecast(new Request('test address'));
    }

    /**
     * @test
     */
    public function itShouldReturnCorrectForecastData(): void
    {
        $this->addMockHandler(200, $this->getFile('geocoder.json'));
        $this->addMockHandler(200, $this->getFile('weatherkit/forecast.json'));

        $request = (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
            ->atDates([Carbon::parse('2020-01-01 13:59'), Carbon::parse('2020-01-02 13:59')])
            ->withUnits( 'm')
            ->withLocale('en');

        $responses = (new WeatherKit)->getForecast($request);
        $this->checkWeatherResponse($responses);
    }

    /**
     * @test
     */
    public function itShouldReturnCorrectHistoricalData(): void
    {
        $this->addMockHandler(200, $this->getFile('geocoder.json'));
        $this->addMockHandler(200, $this->getFile('weatherkit/historical-1.json'));
        $this->addMockHandler(200, $this->getFile('weatherkit/historical-2.json'));

        $requests = (new Request('1 Infinite Loop, Cupertino, CA 95014, USA'))
            ->withUnits( 'm')
            ->withLocale('en');

        $responses = (new WeatherKit)->getForecast($requests);
        $this->checkWeatherResponse($responses);
    }

    /**
     * @test
     */
    public function itShouldMapExtendedConditionCodesToIcons(): void
    {
        $provider = new class extends WeatherKit {
            public function mapIcon(string $conditionCode): string
            {
                return $this->convertIcon($conditionCode);
            }
        };

        $this->assertSame(Provider::WEATHER_ICON_SLEET, $provider->mapIcon('WintryMix'));
        $this->assertSame(Provider::WEATHER_ICON_WIND, $provider->mapIcon('Thunderstorms'));
    }
}
