<?php

namespace Vemcogroup\Weather;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Spatie\Geocoder\Geocoder;
use Illuminate\Support\Facades\Cache;
use Vemcogroup\Weather\Exceptions\WeatherException;

class Request
{

    private $url;
    private $key;
    private $units;
    private $dates;
    private $locale;
    private $address;
    private $geocode;
    private $options;
    private $response;

    public function __construct(string $address)
    {
        $this->dates = [];
        $this->options = [];
        $this->address = $address;

        $this->units = 's';
        $this->locale = 'en';
    }

    public function lookupGeocode(): void
    {
        $cacheKey = md5('laravel-weather-geocode-' . $this->address);
        try {
            if (!($this->geocode = Cache::get($cacheKey))) {
                $response = (new Geocoder(app(Client::class)))
                    ->setApiKey(config('geocoder.key', ''))
                    ->getCoordinatesForAddress($this->address);
                if ($response['lat'] === 0 && $response['lng'] === 0) {
                    throw WeatherException::invalidAddress($this->address, $response['formatted_address']);
                }
                Cache::put($cacheKey, $response, now()->addDays(10));
                $this->geocode = $response;
            }
        } catch (\Exception $e) {
            throw WeatherException::invalidAddress($this->address, $e->getMessage());
        }
    }

    protected function getMidday(): Carbon
    {
        return now()->setHour(config('weather.midday.hour'))->setMinute(config('weather.midday.minute'));
    }

    public function getHttpQuery($type = 'GET'): string
    {
        if ($type === 'GET') {
            return http_build_query($this->options);
        }

        return 'JSON_STRING';
    }

    public function getCacheResponse(): ?object
    {
        return Cache::get(md5('laravel-weather-' . $this->url));
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getLongitude()
    {
        return $this->geocode['lng'] ?? null;
    }

    public function getLatitude()
    {
        return $this->geocode['lat'] ?? null;
    }

    public function getDates(): array
    {
        return count($this->dates) ? $this->dates : [$this->getMidday()];
    }

    public function getKey(): ?string
    {
        return $this->key ?: $this->getMidday()->format('Y-m-d H:i');
    }

    public function withOption(string $name, $value = null): Request
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function withUnits(string $units): Request
    {
        $this->units = $units;

        return $this;
    }

    public function getUnits(): string
    {
        return $this->units;
    }

    public function withLocale(string $locale = null): Request
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function atDates(array $dates): Request
    {
        $this->dates = $dates;

        return $this;
    }

    public function setKey(string $key): Request
    {
        $this->key = $key;

        return $this;
    }

    public function setResponse($response): void
    {
        $this->response = $response;
    }

    public function getResponse($asType = null)
    {
        if($asType === 'array') {
            return (array) $this->response;
        }

        if ($asType === 'string') {
            return json_encode((array) $this->response);
        }

        return $this->response;
    }

    public function getForecast(): Response
    {
        return new Response((object) $this->response);
    }

    public function setUrl(string $url): Request
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl()
    {
        if (! $this->url) {
            throw WeatherException::noUrl();
        }

        return $this->url;
    }
}