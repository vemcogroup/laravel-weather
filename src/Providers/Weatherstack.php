<?php

namespace Vemcogroup\Weather\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Response;

class Weatherstack extends Provider
{
    protected $url = 'http://api.weatherstack.com/';

    public function getData($requests): array
    {
        $this->requests = $requests;
        $this->buildRequests();
        $this->processRequests();

        return $this->formatResponse();
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

    private function formatResponse(): array
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
                        $formattedResponses[$date->format('Y-m-d H:i')] = new Response([
                            'latitude' => (float) $response->location->lat,
                            'longitude' => (float) $response->location->lon,
                            'timezone' => $response->location->timezone_id,
                            'currently' => [
                                'time' => $date->timestamp,
                                'summary' => $responseHour[$hour]->weather_descriptions[0][0],
                                'icon' => $responseHour[$hour]->weather_code,
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

                $current = $response->current;
                $time = Carbon::parse($current->observation_time);

                $formattedResponses[$request->getKey()] = new Response([
                    'latitude' => (float) $response->location->lat,
                    'longitude' => (float) $response->location->lon,
                    'timezone' => $response->location->timezone_id,
                    'currently' => [
                        'time' => $time->timestamp,
                        'summary' => $current->weather_descriptions[0],
                        'icon' => $current->weather_icons[0],
                        'precipIntensity' => $current->precip,
                        'precipProbability' => 0,                           // Not available
                        'temperature' => $current->temperature,
                        'apparentTemperature' => $current->feelslike,
                        'dewPoint' => 0,                                    // Not available
                        'humidity' => $current->humidity,
                        'pressure' => $current->pressure,
                        'windSpeed' => $current->wind_speed,
                        'windGust' => 0,                                    // Not available
                        'windBearing' => $current->wind_degree,
                        'cloudCover' => $current->cloudcover,
                        'uvIndex' => $current->uv_index,
                        'visibility' => $current->visibility,
                        'ozone' => 0,                                       // Not available
                    ],
                    'offset' => $response->location->utc_offset,
                ]);
            }
        }

        return $formattedResponses;
    }
}
