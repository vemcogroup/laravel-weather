<?php

namespace Vemcogroup\Weather\Providers;

use Vemcogroup\Weather\Request;
use Illuminate\Support\Facades\Cache;
use Vemcogroup\Weather\Objects\Forecast;

class Darksky extends Provider
{
    protected $url = 'https://api.darksky.net/forecast/';

    public function getWeather($requests): array
    {
        $builtRequest = $this->buildRequest($requests);

        $responses = $this->processRequest($builtRequest['request_urls']);

        return array_merge($builtRequest['cached_result'], $responses);
    }

    public function getForecast(Request $request): Forecast
    {
        $options = $request->getHttpQuery();

        $url = $this->url . $this->apiKey
            . '/' . $request->getLatitude() . ',' . $request->getLongitude()
            . ($options ? "?$options" : '');

        $key = md5('laravel-weather-' . $url);

        if (!($cachedResponse = Cache::get($key))) {
            $response = ($this->client->request('GET', $url))->getBody();

            Cache::put(
                md5('laravel-weather-' . $url),
                json_decode($response),
                now()->addDay()
            );
        } else {
            $response = $cachedResponse;
        }

        return new Forecast(json_decode($response, true));
    }

    private function buildRequest($requests): array
    {
        $result = [
            'request_urls' => [],
            'cached_result' => [],
        ];

        /** @var Request $request **/
        foreach ($requests as $request) {

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

            $requestUrl = $this->url . $this->apiKey
                . "/$latitude,$longitude"
                . ($time ? ",$time" : '')
                . ($options ? "?$options" : '');

            $key = md5('laravel-weather-' . $requestUrl);

            if (!($cacheResult = Cache::get($key))) {
                $result['request_urls'][$request->getKey()] = $requestUrl;
            } else {
                $result['cached_result'][$request->getKey()] = $cacheResult;
            }
        }

        return $result;
    }
}
