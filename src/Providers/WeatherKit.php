<?php

namespace Vemcogroup\Weather\Providers;

use stdClass;
use Exception;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Response;
use Illuminate\Support\Collection;
use Vemcogroup\Weather\Exceptions\WeatherException;

use function count;
use function openssl_pkey_get_private;

class WeatherKit extends Provider
{
    protected $url = 'https://weatherkit.apple.com/api/v1/weather/';

    /**
     * @throws WeatherException
     */
    public function __construct()
    {
        try {
            parent::__construct();
        } catch (WeatherException $exception) {
            if ($exception->getCode() !== WeatherException::NO_API_KEY_ERROR_CODE) {
                throw $exception;
            }
        }

        if (
            config('weather.providers.weatherkit.alg') === null
            || config('weather.providers.weatherkit.kid') === null
            || config('weather.providers.weatherkit.id') === null
            || config('weather.providers.weatherkit.iss') === null
            || config('weather.providers.weatherkit.sub') === null
        ) {
            throw new WeatherException('Missing or misconfigured WeatherKit settings, please add/edit it in .env', 1001);
        }

        try {
            $this->getToken();
        } catch (Exception $e) {
            throw new WeatherException('Missing or misconfigured WeatherKit settings, please add/edit it in .env', 1001);
        }

        if (!app()->environment('testing')) {
            $this->client = app(Client::class, [
                'config' => [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getToken(),
                    ],
                ]
            ]);
        }
    }

    public function getForecast($requests): Collection
    {
        $this->setupRequests($requests);
        $this->buildRequests();
        $this->processRequests();

        return collect($this->formatResponse());
    }

    public function getHistorical($requests): Collection
    {
        $this->setupRequests($requests);
        $this->buildRequests(self::WEATHER_TYPE_HISTORICAL);
        $this->processRequests();

        return collect($this->formatResponse());
    }

    private function buildRequests($type = self::WEATHER_TYPE_FORECAST): void
    {
        $requests = [];

        foreach ($this->requests as $request) {
            $request->lookupGeocode();
            $latitude = $request->getLatitude();
            $longitude = $request->getLongitude();
            $locale = $request->getLocale();

            if ($request->getUnits() === self::WEATHER_UNITS_METRIC) {
                $request->withUnits('si');
            }

            if ($request->getUnits() === self::WEATHER_UNITS_FAHRENHEIT) {
                $request->withUnits('us');
            }

            if ($type === self::WEATHER_TYPE_FORECAST) {
                $dateRequest = clone $request;
                $url = $this->url . "$locale/$latitude/$longitude?dataSets=forecastDaily&timezone=" . $request->getTimezone() . '&currentAsOf=' . now()->setMinute(0)->setSecond(0)->toIso8601ZuluString();
                $dateRequest->setUrl($url);
                $requests[] = $dateRequest;
            }

            if ($type === self::WEATHER_TYPE_HISTORICAL) {
                foreach ($request->getDates() as $date) {
                    $dateRequest = clone $request;
                    $url = $this->url . "$locale/$latitude/$longitude?dataSets=currentWeather&timezone=" . $request->getTimezone() . '&currentAsOf=' . $date->toIso8601ZuluString();
                    $dateRequest->setKey($date->format('Y-m-d H:i'));
                    $dateRequest->setUrl($url);
                    $requests[] = $dateRequest;
                }
            }
        }

        if (count($requests)) {
            $this->requests = $requests;
        }
    }

    protected function verifyResponse(stdClass $response): bool
    {
        return isset($response->currentWeather) || isset($response->forecastDaily);
    }

    private function formatResponse(): array
    {
        $result = [];

        /** @var Request $request */
        foreach ($this->requests as $request) {
            $response = $request->getResponse();

            if (!isset($response->currentWeather) && !isset($response->forecastDaily)) {
                continue;
            }

            $result[$request->getKey()] = $this->formatSingleResponse($response);
        }

        return $result;
    }

    private function formatSingleResponse($response): Response
    {
        /** @var Request $request */
        $request = Arr::first($this->requests);

        $data = [];

        if (isset($response->currentWeather)) {
            $data = $this->getCurrentWeatherDataFromResponse($response->currentWeather, $request);
        } elseif (isset($response->forecastDaily)) {
            $data = $this->getForecastWeatherDataFromResponse($response->forecastDaily, $request);
        }

        return new Response($data);
    }

    protected function convertIcon(string $conditionCode): string
    {
        $map = [
            'Clear' => self::WEATHER_ICON_CLEAR_DAY,
            'Breezy' => self::WEATHER_ICON_WIND,
            'Cloudy' => self::WEATHER_ICON_CLOUDY,
            'Drizzle' => self::WEATHER_ICON_RAIN,
            'MostlyClear' => self::WEATHER_ICON_PARTLY_CLOUDY_DAY,
            'MostlyCloudy' => self::WEATHER_ICON_CLOUDY,
            'PartlyCloudy' => self::WEATHER_ICON_PARTLY_CLOUDY_DAY,
            "Dust" => self::WEATHER_ICON_FOG,
            "Fog" => self::WEATHER_ICON_FOG,
            "Haze" => self::WEATHER_ICON_FOG,
            "ScatteredThunderstorms" => self::WEATHER_ICON_WIND,
            "Smoke" => self::WEATHER_ICON_FOG,
            "Windy" => self::WEATHER_ICON_WIND,
            "HeavyRain" => self::WEATHER_ICON_RAIN,
            "Rain" => self::WEATHER_ICON_RAIN,
            "Showers" => self::WEATHER_ICON_RAIN,
            "Flurries" => self::WEATHER_ICON_SLEET,
            "HeavySnow" => self::WEATHER_ICON_SNOW,
            "MixedRainAndSleet" => self::WEATHER_ICON_SLEET,
            "MixedRainAndSnow" => self::WEATHER_ICON_SNOW,
            "MixedRainfall" => self::WEATHER_ICON_RAIN,
            "MixedSnowAndSleet" => self::WEATHER_ICON_SLEET,
            "ScatteredShowers" => self::WEATHER_ICON_RAIN,
            "ScatteredSnowShowers" => self::WEATHER_ICON_SNOW,
            "Sleet" => self::WEATHER_ICON_SLEET,
            "Snow" => self::WEATHER_ICON_SNOW,
            "SnowShowers" => self::WEATHER_ICON_SNOW,
            "Blizzard" => self::WEATHER_ICON_SNOW,
            "BlowingSnow" => self::WEATHER_ICON_SNOW,
            "FreezingDrizzle" => self::WEATHER_ICON_RAIN,
            "FreezingRain" => self::WEATHER_ICON_RAIN,
            "Frigid" => self::WEATHER_ICON_SLEET,
            "Hail" => self::WEATHER_ICON_SNOW,
            "Hot" => self::WEATHER_ICON_CLEAR_DAY,
            "Hurricane" => self::WEATHER_ICON_WIND,
            "IsolatedThunderstorms" => self::WEATHER_ICON_WIND,
            "SevereThunderstorm" => self::WEATHER_ICON_WIND,
            "Thunderstorm" => self::WEATHER_ICON_WIND,
            "Tornado" => self::WEATHER_ICON_WIND,
            "TropicalStorm" => self::WEATHER_ICON_WIND
        ];

        return $map[$conditionCode] ?? self::WEATHER_ICON_NA;
    }

    private function getToken(): string
    {
        $privateKey = openssl_pkey_get_private(config('weather.providers.weatherkit.private-key'));

        $header = [
            'alg' => config('weather.providers.weatherkit.alg'),
            'kid' => config('weather.providers.weatherkit.kid'),
            'id' => config('weather.providers.weatherkit.id'),
        ];

        $payload = [
            'iss' => config('weather.providers.weatherkit.iss'),
            'sub' => config('weather.providers.weatherkit.sub'),
            'iat' => now('UTC')->timestamp,
            'exp' => now('UTC')->addMinute()->timestamp,
        ];

        return JWT::encode($payload, $privateKey, config('weather.providers.weatherkit.alg'), null, $header);
    }

    protected function getCurrentWeatherDataFromResponse($weatherData, Request $request): array
    {
        return [
            'latitude' => (float) $weatherData->metadata->latitude,
            'longitude' => (float) $weatherData->metadata->longitude,
            'timezone' => $request->getTimezone(),
            'currently' => [
                'time' => Carbon::parse($weatherData->asOf),
                'summary' => $weatherData->conditionCode,
                'icon' => $this->convertIcon($weatherData->conditionCode),
                'precipIntensity' => $weatherData->precipitationIntensity,
                'precipProbability' => 0,
                'temperature' => $request->getUnits() === 'si' ? $weatherData->temperature : $this->celsiusToFahrenheit($weatherData->temperature),
                'apparentTemperature' => $request->getUnits() === 'si' ? $weatherData->temperatureApparent : $this->celsiusToFahrenheit($weatherData->temperatureApparent),
                'dewPoint' => $request->getUnits() === 'si' ? $weatherData->temperatureDewPoint : $this->celsiusToFahrenheit($weatherData->temperatureDewPoint),
                'humidity' => $weatherData->humidity,
                'pressure' => $weatherData->pressure,
                'windSpeed' => $request->getUnits() === 'si' ? $weatherData->windSpeed : $this->kmToMiles($weatherData->windSpeed),
                'windGust' => $request->getUnits() === 'si' ? $weatherData->windGust : $this->kmToMiles($weatherData->windGust),
                'windBearing' => $weatherData->windDirection,
                'cloudCover' => $weatherData->cloudCover,
                'uvIndex' => $weatherData->uvIndex,
                'visibility' => $weatherData->visibility,
                'ozone' => 0,
            ],
            'offset' => 0,
            'daily' => [
                'icon' => $this->convertIcon($weatherData->conditionCode),
                'summary' => $weatherData->conditionCode,
            ],
        ];
    }

    protected function getForecastWeatherDataFromResponse($weatherData, Request $request): array
    {
        $data = [
            'latitude' => (float) $weatherData->metadata->latitude,
            'longitude' => (float) $weatherData->metadata->longitude,
            'timezone' => $request->getTimezone(),
            'currently' => [
                'time' => Carbon::parse($weatherData->days[0]->forecastStart),
                'summary' => $weatherData->days[0]->conditionCode,
                'icon' => $this->convertIcon($weatherData->days[0]->conditionCode),
                'precipIntensity' => $weatherData->days[0]->precipitationAmount,
                'precipProbability' => $weatherData->days[0]->precipitationChance,
                'temperature' => $request->getUnits() === 'si' ? $weatherData->days[0]->temperatureMax : $this->celsiusToFahrenheit($weatherData->days[0]->temperatureMax),
                'apparentTemperature' => 0,
                'dewPoint' => 0,
                'humidity' => $weatherData->days[0]->daytimeForecast->humidity,
                'pressure' => 0,
                'windSpeed' => $request->getUnits() === 'si' ? $weatherData->days[0]->daytimeForecast->windSpeed : $this->kmToMiles($weatherData->days[0]->daytimeForecast->windSpeed),
                'windGust' => 0,
                'windBearing' => $weatherData->days[0]->daytimeForecast->windDirection,
                'cloudCover' => $weatherData->days[0]->daytimeForecast->cloudCover,
                'uvIndex' => $weatherData->days[0]->maxUvIndex,
                'visibility' => 0,
                'ozone' => 0,
            ],
            'offset' => 0,
            'daily' => [
                'icon' => $this->convertIcon($weatherData->days[0]->conditionCode),
                'summary' => $weatherData->days[0]->conditionCode,
            ],
        ];

        foreach ($weatherData->days as $day) {
            $data['daily']['data'][] = [
                'time' => $day->forecastStart ?? null,
                'icon' => $this->convertIcon($day->conditionCode) ?? self::WEATHER_ICON_NA,
                'summary' => $day->conditionCode ?? null,
                'temperatureMin' => $day->temperatureMin ?? null,
                'temperatureMax' => $day->temperatureMax ?? null,
            ];
        }

        return $data;
    }
}
