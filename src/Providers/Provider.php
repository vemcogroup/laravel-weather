<?php


namespace Vemcogroup\Weather\Providers;

use GuzzleHttp\Client;
use Vemcogroup\Weather\Request;
use Illuminate\Support\Collection;
use GuzzleHttp\Promise\EachPromise;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Vemcogroup\Weather\Exceptions\WeatherException;

abstract class Provider
{
    protected const WEATHER_TYPE_FORECAST = 'forecast';
    protected const WEATHER_TYPE_HISTORICAL = 'historical';

    protected $url;
    protected $apiKey;
    protected $client;
    protected $requests;
    protected $threads = 50;

    public function __construct()
    {
        $this->requests = [];

        if (!$this->apiKey = config('weather.api_key')) {
            throw WeatherException::noApiKey();
        }

        $this->client = app(Client::class);
    }

    abstract public function getForecast($requests): Collection;
    abstract public function getHistorical($requests): Collection;

    protected function setupRequests($requests): void
    {
        $this->requests = is_array($requests) ? $requests : [$requests];
    }

    protected function processRequests(): void
    {
       $promises = (function() {
            /** @var Request $request */
            foreach ($this->requests as $request) {
                if ($cachedResponse = $request->getCacheResponse()) {
                    $request->setResponse($cachedResponse);
                    yield new FulfilledPromise($cachedResponse);
                    continue;
                }

                yield $this->client->getAsync($request->getUrl())->then(function (GuzzleResponse $response) use ($request) {
                    $content = json_decode($response->getBody());
                    Cache::put(md5('laravel-weather-' . $request->getUrl()), $content, now()->addDay());
                    $request->setResponse($content);
                });
            }
        })();

        $eachPromise = new EachPromise($promises, [
            'concurrency' => $this->threads,
            'fulfilled' => function ($profile) {},
            'rejected' => function ($reason) {
                throw WeatherException::communicationError($reason);
            }
        ]);
        $eachPromise->promise()->wait();
    }
}
