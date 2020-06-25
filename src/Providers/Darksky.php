<?php

namespace Vemcogroup\Weather\Providers;

use Carbon\Carbon;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Objects\Forecast;

class Darksky extends Provider
{
    protected $url = 'https://api.darksky.net/forecast/';

    public function getWeather($requests): array
    {
        $responses = [];
        $this->requests = $requests;

        $this->buildRequest();
        $this->processRequests();

        /** @var Request $request */
        foreach ($this->requests as $request) {
            $responses[$request->getKey()] = $request->getForecast();
        }

        return $responses;
    }

    public function getForecast(Request $request): Forecast
    {
        $options = $request->getHttpQuery();

        $url = $this->url . $this->apiKey
            . '/' . $request->getLatitude() . ',' . $request->getLongitude()
            . ($options ? "?$options" : '');

        $request->setUrl($url);
        $this->requests[] = $request;
        $this->processRequests();

        return $this->requests[0]->getForecast();
    }

    private function buildRequest(): void
    {
        $requests = [];

        /** @var Request $request **/
        foreach ($this->requests as $request) {
            $latitude = $request->getLatitude();
            $longitude = $request->getLongitude();
            $options = $request->getHttpQuery();

            /** @var Carbon $date */
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
