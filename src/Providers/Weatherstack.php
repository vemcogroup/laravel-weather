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

            if ($type === self::WEATHER_TYPE_FORECAST) {
                $url .= 'forecast';
                $request->withOption('forecast_days', 7)
                    ->withOption('hourly', 1)
                    ->withOption('interval', 24);
            }

            if ($type === self::WEATHER_TYPE_HISTORICAL) {
                $url .= 'historical';
                $request->withOption('hourly', 1)->withOption('interval', 1);
                $dates = [];
                /** @var Carbon $date */
                foreach ($request->getDates() as $date) {
                    $dates[] = $date->format('Y-m-d');
                }

                $request->withOption('historical_date', implode(';', $dates));
            }

            $options = $request->getHttpQuery();

            $url .= ($options ? "?$options" : '');

            $request->setUrl($url);
        }
    }

    private function formatResponse(): array
    {
        $result = [];

        /** @var Request $request */
        foreach ($this->requests as $request) {
            $response = $request->getResponse();

            if (isset($response->historical)) {
                foreach ($response->historical as $dateKey => $responseData) {
                    /** @var Carbon $date */
                    foreach ($request->getDates() as $date) {
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
                'time' => isset($data->observation_time) ? Carbon::parse($data->observation_time) : Carbon::parse(now()->format('Y-m-d') . ' ' . $data->time),
                'summary' => $data->weather_descriptions[0],
                'icon' => $data->weather_code,
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
                'icon' => $response->current->weather_code,
                'summary' => $response->current->weather_descriptions[0] ?? null,
            ],
        ];

        if (isset($response->forecast)) {
            foreach ($response->forecast as $day) {
                $data['daily']['data'][] = [
                    'time' => $day->date_epoch ?? null,
                    'icon' => $day->hourly[0]->weather_code ?? null,
                    'summary' => $day->hourly[0]->weather_descriptions[0] ?? null,
                    'temperatureMin' => $day->mintemp ?? null,
                    'temperatureMax' => $day->maxtemp ?? null,
                ];
            }
        }

        return new Response($data);
    }
}
