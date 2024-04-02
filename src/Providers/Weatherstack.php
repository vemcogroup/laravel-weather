<?php

namespace Vemcogroup\Weather\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Response;
use Illuminate\Support\Collection;

class Weatherstack extends Provider
{
    protected $url = 'http://api.weatherstack.com/';

    public function getHistorical($requests): Collection
    {
        $this->setupRequests($requests);
        $this->buildRequests(self::WEATHER_TYPE_HISTORICAL);
        $this->processRequests();

        return collect($this->formatResponse());
    }

    public function getForecast($requests): Collection
    {
        $this->setupRequests($requests);
        $this->buildRequests(self::WEATHER_TYPE_FORECAST);
        $this->processRequests();

        return collect($this->formatResponse());
    }

    private function buildRequests($type = self::WEATHER_TYPE_FORECAST): void
    {
        /** @var Request $request */
        foreach ($this->requests as $request) {
            $url = $this->url;
            $request->withOption('access_key', $this->apiKey)
                ->withOption('query', $request->getAddress());

            if($request->getLocale() === 'en') {
                $request->withLocale(null);
            }

            if ($type === self::WEATHER_TYPE_FORECAST) {
                $url .= 'forecast';
                $request->withOption('forecast_days', 7)
                    ->withOption('hourly', 1)
                    ->withOption('interval', config('weather.providers.weatherstack.intervals.forecast'));
            }

            if ($type === self::WEATHER_TYPE_HISTORICAL) {
                $url .= 'historical';
                $request->withOption('hourly', 1)->withOption('interval', config('weather.providers.weatherstack.intervals.historical'));
                $dates = [];
                /** @var Carbon $date */
                if (config('weather.historical_date_status')) {
                    foreach ($request->getDates() as $date) {
                        $dates[] = $date->format('Y-m-d');
                    }

                    $request->withOption('historical_date', implode(';', $dates));
                }
            }

            $options = $request->getHttpQuery();

            $url .= "?units=" . $request->getUnits()
            . ($request->getLocale() ? "&language=" . $request->getLocale() : '')
            . ($options ? "&$options" : '');

            $request->setUrl($url);
        }
    }

    private function formatResponse(): array
    {
        $result = [];

        /** @var Request $request */
        foreach ($this->requests as $request) {
            if (!config('weather.formatted_response.historical')) {
                return $request->getResponse('array');
            }
            $response = $request->getResponse();

            if (isset($response->historical)) {
                /** @var Carbon $date */
                foreach ($request->getDates() as $date) {
                    $key = $date->format('Y-m-d');
                    if (property_exists($response->historical, $key)) {
                        $responseData = $response->historical->$key;
                        $result[$date->format('Y-m-d H:i')] = $this->formatSingleResponse($response, $responseData->hourly[$date->hour]);
                    }
                }
            } else {
                $result[$request->getKey()] = $this->formatSingleResponse($response, $response->current);
            }
        }

        return $result;
    }

    private function formatSingleResponse($response, $data): Response
    {
        $data = [
            'latitude' => (float) $response->location->lat,
            'longitude' => (float) $response->location->lon,
            'timezone' => $response->location->timezone_id,
            'currently' => [
                'time' => isset($data->observation_time) ? Carbon::parse(strtotime($data->observation_time)) : Carbon::parse(strtotime(now()->format('Y-m-d') . ' ' . $data->time)),
                'summary' => $data->weather_descriptions[0],
                'icon' => $this->convertIcon($data->weather_code),
                'precipIntensity' => $data->precip,
                'precipProbability' => 0,                           // Not available
                'temperature' => $data->temperature,
                'apparentTemperature' => $data->feelslike,
                'dewPoint' => 0,                                    // Not available
                'humidity' => $data->humidity,
                'pressure' => $data->pressure,
                'windSpeed' => $data->wind_speed,
                'windGust' => 0,                                    // Not available
                'windBearing' => $data->wind_degree,
                'cloudCover' => $data->cloudcover,
                'uvIndex' => $data->uv_index,
                'visibility' => $data->visibility,
                'ozone' => 0,                                       // Not available
            ],
            'offset' => $response->location->utc_offset,
            'daily' => [
                'icon' => $this->convertIcon($response->current->weather_code),
                'summary' => $response->current->weather_descriptions[0] ?? null,
            ],
        ];

        if (isset($response->forecast)) {
            foreach ($response->forecast as $day) {
                $data['daily']['data'][] = [
                    'time' => $day->date_epoch ?? null,
                    'icon' => $this->convertIcon($day->hourly[0]->weather_code) ?? self::WEATHER_ICON_NA,
                    'summary' => $day->hourly[0]->weather_descriptions[0] ?? null,
                    'temperatureMin' => $day->mintemp ?? null,
                    'temperatureMax' => $day->maxtemp ?? null,
                ];
            }
        }

        return new Response($data);
    }

    private function convertIcon($code)
    {
        $map = [
            113 => self::WEATHER_ICON_CLEAR_DAY,
            116 => self::WEATHER_ICON_PARTLY_CLOUDY_DAY,
            119 => self::WEATHER_ICON_CLOUDY,
            122 => self::WEATHER_ICON_CLOUDY,
            143 => self::WEATHER_ICON_CLOUDY,
            176 => self::WEATHER_ICON_RAIN,
            179 => self::WEATHER_ICON_SLEET,
            182 => self::WEATHER_ICON_SLEET,
            185 => self::WEATHER_ICON_SLEET,
            200 => self::WEATHER_ICON_RAIN,
            227 => self::WEATHER_ICON_SNOW,
            230 => self::WEATHER_ICON_SNOW,
            248 => self::WEATHER_ICON_FOG,
            260 => self::WEATHER_ICON_FOG,
            263 => self::WEATHER_ICON_RAIN,
            266 => self::WEATHER_ICON_RAIN,
            281 => self::WEATHER_ICON_SLEET,
            284 => self::WEATHER_ICON_SLEET,
            293 => self::WEATHER_ICON_RAIN,
            296 => self::WEATHER_ICON_RAIN,
            299 => self::WEATHER_ICON_RAIN,
            302 => self::WEATHER_ICON_RAIN,
            305 => self::WEATHER_ICON_RAIN,
            308 => self::WEATHER_ICON_RAIN,
            311 => self::WEATHER_ICON_SLEET,
            314 => self::WEATHER_ICON_SLEET,
            317 => self::WEATHER_ICON_SLEET,
            320 => self::WEATHER_ICON_SLEET,
            323 => self::WEATHER_ICON_SNOW,
            326 => self::WEATHER_ICON_SNOW,
            329 => self::WEATHER_ICON_SNOW,
            332 => self::WEATHER_ICON_SNOW,
            335 => self::WEATHER_ICON_SNOW,
            338 => self::WEATHER_ICON_SNOW,
            350 => self::WEATHER_ICON_SLEET,
            353 => self::WEATHER_ICON_RAIN,
            356 => self::WEATHER_ICON_RAIN,
            359 => self::WEATHER_ICON_RAIN,
            362 => self::WEATHER_ICON_SLEET,
            365 => self::WEATHER_ICON_SLEET,
            368 => self::WEATHER_ICON_SNOW,
            371 => self::WEATHER_ICON_SNOW,
            374 => self::WEATHER_ICON_SLEET,
            377 => self::WEATHER_ICON_SLEET,
            386 => self::WEATHER_ICON_RAIN,
            389 => self::WEATHER_ICON_RAIN,
            392 => self::WEATHER_ICON_SNOW,
            395 => self::WEATHER_ICON_SNOW,
        ];

        return $map[$code] ?: self::WEATHER_ICON_NA;

    }
}
