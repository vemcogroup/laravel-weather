<?php

namespace Vemcogroup\Weather\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Objects\Forecast;

class Weatherstack extends Provider
{
    protected $url = 'http://api.weatherstack.com/';

    public function getWeather($requests): array
    {
        $this->requests = $requests;
        $this->buildRequests();
        $this->processRequests();

        return $this->formatHistoricResponse();
    }

    private function buildRequests(): void
    {
        /** @var Request $request */
        foreach ($this->requests as $request) {
            $request->withOption('access_key', $this->apiKey)
                ->withOption('query', $request->getLatitude() . ',' . $request->getLongitude())
                ->withOption('hourly', 1)
                ->withOption('interval', 1);

            $dates = [];
            /** @var Carbon $date */
            foreach ($request->getDates() as $date) {
                $dates[] = $date->format('Y-m-d');
            }

            $request->withOption('historical_date', implode(';', $dates));

            $options = $request->getHttpQuery();

            $url = $this->url
                . 'historical'
                . ($options ? "?$options" : '');

            $request->setUrl($url);
        }
    }

    private function formatHistoricResponse(): array
    {
        $formattedResponses = [];

        /** @var Request $request */
        foreach ($this->requests as $request) {
            $response = $request->getResponse();
            if (isset($response->historical)) {
                foreach ($response->historical as $dateKey => $responseData) {
                    /** @var Carbon $date */
                    foreach ($request->getDates() as $date) {
                        $hour = $date->hour;
                        $responseHour = $responseData->hourly;
                        $formattedResponses[$date->format('Y-m-d H:i')] = (object) ([
                            'latitude' => (float) $response->location->lat,
                            'longitude' => (float) $response->location->lon,
                            'timezone' => $response->location->timezone_id,
                            'currently' => (object) [
                                'time' => $date->timestamp,
                                'summary' => $responseHour[$hour]->weather_descriptions[0][0],
                                'icon' => self::mapWeatherIcon($responseHour[$hour]->weather_code),
                                'precipIntensity' => $responseHour[$hour]->precip,
                                'precipProbability' => $responseHour[$hour]->chanceofrain,
                                'temperature' => $responseHour[$hour]->temperature,
                                'apparentTemperature' => $responseHour[$hour]->feelslike,
                                'dewPoint' => $responseHour[$hour]->dewpoint,
                                'humidity' => $responseHour[$hour]->humidity,
                                'pressure' => $responseHour[$hour]->pressure,
                                'windSpeed' => $responseHour[$hour]->wind_speed,
                                'windGust' => $responseHour[$hour]->windgust,
                                'windBearing' => $responseHour[$hour]->wind_degree,
                                'cloudCover' => $responseHour[$hour]->cloudcover,
                                'uvIndex' => $responseHour[$hour]->uv_index,
                                'visibility' => $responseHour[$hour]->visibility,
                                'ozone' => 0, // This does not exist in weatherstack?
                            ],
                            'offset' => $response->location->utc_offset,
                        ]);
                    }
                }
            } else {
                $time = Carbon::parse('g:i a', $response->current->observation_time)->format('H:i');
                $key = explode(' ', $response->location->localtime)[0] . ' ' . $time;

                $current = $response['current'];

                $formattedResponses[$key] = (object) ([
                    'latitude' => (float) $response->location->lat,
                    'longitude' => (float) $response->location->lon,
                    'timezone' => $response->location->timezone_id,
                    'currently' => [
                        'time' => $time,
                        'summary' => $current['weather_descriptions'][0],
                        'icon' => $current['weather_icons'][0],
                        'precipIntensity' => $current['precip'],
                        'precipProbability' => 0,                           // Not available
                        'temperature' => $current['temperature'],
                        'apparentTemperature' => $current['feelslike'],
                        'dewPoint' => 0,                                    // Not available
                        'humidity' => $current['humidity'],
                        'pressure' => $current['pressure'],
                        'windSpeed' => $current['wind_speed'],
                        'windGust' => 0,                                    // Not available
                        'windBearing' => $current['wind_degree'],
                        'cloudCover' => $current['cloudcover'],
                        'uvIndex' => $current['uv_index'],
                        'visibility' => $current['visibility'],
                        'ozone' => 0,                                       // Not available
                    ],
                    'offset' => $response->location->utc_offset,
                ]);
            }
        }

        return $formattedResponses;
    }

    public static function mapWeatherIcon($weatherCode): string
    {
        $map = [
            113 => 'clear-day',
            116 => 'partly-cloudy-day',
            119 => 'cloudy',
            122 => 'cloudy',
            143 => 'cloudy',
            176 => 'rain',
            179 => 'sleet',
            182 => 'sleet',
            185 => 'sleet',
            200 => 'rain',
            227 => 'snow',
            230 => 'snow',
            248 => 'fog',
            260 => 'fog',
            263 => 'rain',
            266 => 'rain',
            281 => 'sleet',
            284 => 'sleet',
            293 => 'rain',
            296 => 'rain',
            299 => 'rain',
            302 => 'rain',
            305 => 'rain',
            308 => 'rain',
            311 => 'sleet',
            314 => 'sleet',
            317 => 'sleet',
            320 => 'sleet',
            323 => 'snow',
            326 => 'snow',
            329 => 'snow',
            332 => 'snow',
            335 => 'snow',
            338 => 'snow',
            350 => 'sleet',
            353 => 'rain',
            356 => 'rain',
            359 => 'rain',
            362 => 'sleet',
            365 => 'sleet',
            368 => 'snow',
            371 => 'snow',
            374 => 'sleet',
            377 => 'sleet',
            386 => 'rain',
            389 => 'rain',
            392 => 'snow',
            395 => 'snow',
        ];

        return $map[$weatherCode] ?: 'na';
    }

    public function getForecast(Request $request): Forecast
    {
        $request->withOption('access_key', $this->apiKey)
            ->withOption('query', $request->getLatitude() . ',' .$request->getLongitude())
            ->withOption('forecast_days', 7)
            ->withOption('hourly', 1)
            ->withOption('interval', 24);

        $options = $request->getHttpQuery();

        $url = $this->url . 'forecast' . ($options ? "?$options" : '');

        $request->setUrl($url);
        $this->requests[] = $request;
        $this->processRequests();

        return new Forecast($this->formatForecastResponse());
    }

    private function formatForecastResponse(): array
    {
        $response = $this->requests[0]->getResponse();

        $formattedArray = [
            'latitude' => $response->location ? $response->location->lat : null,
            'longitude' => $response->location ? $response->location->lon : null,
            'timezone' => $response->location ? $response->location->timezone_id : null,
            'offset' => $response->location ? $response->location->utc_offset : null,
            'currently' => [
                'temperature' => $response->current ? $response->current->temperature : null,
            ],
            'hourly' => [
                'icon' => self::mapWeatherIcon($response->current->weather_code),
                'summary' => $response->current->weather_descriptions[0] ?? null,
                'data' => [
                    [
                        'time' => (isset($response->current->observation_time)) ? Carbon::parse($response->current->observation_time)->getTimestamp() : null,
                    ],
                ],
            ],
            'daily' => [
                'icon' => self::mapWeatherIcon($response->current->weather_code),
                'summary' => $response->current->weather_descriptions[0] ?? null,
            ],
        ];

        if (isset($response->forecast)) {
            $index = 0;
            foreach ($response->forecast as $data) {
                $formattedArray['daily']['data'][$index]['time'] = $data->date_epoch ?? null;
                $formattedArray['daily']['data'][$index]['icon'] = self::mapWeatherIcon($data->hourly[0]->weather_code ?? null);
                $formattedArray['daily']['data'][$index]['summary'] = $data->hourly[0]->weather_descriptions[0] ?? null;
                $formattedArray['daily']['data'][$index]['temperatureMin'] = $data->mintemp ?? null;
                $formattedArray['daily']['data'][$index]['temperatureMax'] = $data->maxtemp ?? null;
                $index++;
            }
        }

        return $formattedArray;
    }
}
