<?php


namespace Vemcogroup\Weather\Providers;

use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use Vemcogroup\Weather\Request;
use Illuminate\Support\Facades\Cache;
use Vemcogroup\Weather\Objects\Forecast;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Vemcogroup\Weather\Exceptions\WeatherException;

abstract class Provider
{
    protected $url;
    protected $apiKey;
    protected $client;
    protected $threads = 50;

    public function __construct()
    {
        if (!$this->apiKey = config('weather.api_key')) {
            throw WeatherException::noApiKey();
        }

        $this->client = app(Client::class);
    }

    abstract public function getWeather($requests): array;
    abstract public function getForecast(Request $request): Forecast;

    protected function processRequest($requestUrls): array
    {
        $requests = [];
        $responses = [];

        foreach ($requestUrls as $key => $url) {
            $requests[$key] = new GuzzleRequest('GET', $url);
        }

        $pool = new Pool($this->client, $requests, [
            'concurrency' => $this->threads,
            'fulfilled' => function (GuzzleResponse $response, $key) use (&$responses, $requests) {
                $content = json_decode($response->getBody());
                $responses[$key] = $content;
                Cache::put(md5('laravel-weather-' . $requests[$key]->getUri()), $content, now()->addDay());
            },
            'rejected' => function (RequestException $reason, $index) {
                throw WeatherException::communicationError($reason);
            }
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return $responses;
    }
}
