<?php

namespace Vemcogroup\Weather\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Illuminate\Support\Collection;

class Darksky extends Provider
{
    protected $url = 'https://api.darksky.net/forecast/';

    public function getHistorical($requests): Collection
    {
        return $this->getData($requests, self::WEATHER_TYPE_HISTORICAL);
    }

    public function getForecast($requests): Collection
    {
        return $this->getData($requests, self::WEATHER_TYPE_FORECAST);
    }

    private function getData($requests, $type): Collection
    {
        $responses = collect();
        $this->setupRequests($requests);
        $this->buildRequest($type);
        $this->processRequests();

        /** @var Request $request */
        foreach ($this->requests as $request) {
            $responses->put($request->getKey(), $request->getForecast());
        }

        return $responses;
    }

    private function buildRequest($type = self::WEATHER_TYPE_FORECAST): void
    {
        $requests = [];

        /** @var Request $request **/
        foreach ($this->requests as $request) {
            $request->lookupGeocode();
            $latitude = $request->getLatitude();
            $longitude = $request->getLongitude();
            $options = $request->getHttpQuery();

            if ($type === self::WEATHER_TYPE_FORECAST) {
                $dateRequest = clone $request;
                $url = $this->url . $this->apiKey
                    . "/$latitude,$longitude"
                    . ($options ? "?$options" : '');
                $dateRequest->setUrl($url);
                $requests[] = $dateRequest;
            }

            if($type === self::WEATHER_TYPE_HISTORICAL) {
                foreach($request->getDates() as $date) {
                    $dateRequest = clone $request;
                    $url = $this->url . $this->apiKey
                        . "/$latitude,$longitude"
                        . ",$date->timestamp"
                        . ($options ? "?$options" : '');
                    $dateRequest->setKey($date->format('Y-m-d H:i'));
                    $dateRequest->setUrl($url);
                    $requests[] = $dateRequest;
                }
            }

           /* if (!empty($request['time'])) {
                $currentTimezone = config('app.timezone');
                //$currentTimezone = user() && user()->user_timezone ? user()->user_timezone : config('app.timezone');
                $dateConverted = new \DateTime($request['time'], new \DateTimeZone($request['timezone']));
                $dateConverted->setTimezone(new \DateTimeZone($currentTimezone));
                $time = $dateConverted->getTimestamp();
            }*/
        }

        if(count($requests)) {
            $this->requests = $requests;
        }
    }
}
