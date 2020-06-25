<?php

namespace Vemcogroup\Weather\Providers;

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
            $responses[$request->getKey()] = $request->getResponse();
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

        return new Forecast(json_decode($this->requests[0]->getResponse('string'), true));
    }

    private function buildRequest(): void
    {
        /** @var Request $request **/
        foreach ($this->requests as $request) {

            $time = $request->getTimestamp();
            $latitude = $request->getLatitude();
            $longitude = $request->getLongitude();

           /* if (!empty($request['time'])) {
                $currentTimezone = config('app.timezone');
                //$currentTimezone = user() && user()->user_timezone ? user()->user_timezone : config('app.timezone');
                $dateConverted = new \DateTime($request['time'], new \DateTimeZone($request['timezone']));
                $dateConverted->setTimezone(new \DateTimeZone($currentTimezone));
                $time = $dateConverted->getTimestamp();
            }*/

            $options = $request->getHttpQuery();

            $url = $this->url . $this->apiKey
                . "/$latitude,$longitude"
                . ($time ? ",$time" : '')
                . ($options ? "?$options" : '');

            $request->setUrl($url);
        }
    }
}
