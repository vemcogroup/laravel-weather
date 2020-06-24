<?php


namespace Vemcogroup\Weather;


use Carbon\Carbon;
use GuzzleHttp\Client;
use Spatie\Geocoder\Geocoder;
use Illuminate\Support\Facades\Cache;
use Vemcogroup\Weather\Exceptions\WeatherException;

class Request
{
    /** @var Carbon $time */
    public $date;
    public $address;
    public $timezone;
    public $key;

    protected $geocode;
    protected $options;

    public function __construct(string $address)
    {
        try {
            $this->address = $address;
            $this->lookupGeocode();
        } catch (WeatherException $e) {
            throw $e;
        }
    }

    protected function lookupGeocode(): void
    {
        $cacheKey = md5('laravel-weather-geocode-' . $this->address);
        try {
            if (!($this->geocode = Cache::get($cacheKey))) {
                $response = (new Geocoder(app(Client::class)))->getCoordinatesForAddress($this->address);
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

    public function getHttpQuery($type = 'GET'): string
    {
        if ($type === 'GET') {
            return http_build_query($this->options);
        }

        return 'JSON_STRING';
    }

    public function getLongitude()
    {
        return $this->geocode['lng'] ?? null;

    }

    public function getLatitude()
    {
        return $this->geocode['lat'] ?? null;
    }

    public function getDate(): ?Carbon
    {
        return $this->date ?? null;
    }

    public function getTimestamp(): int
    {
        return $this->date ? $this->date->timestamp : now()->timestamp;
    }

    public function getKey(): ?string
    {
        if ($this->key) {
            return $this->key;
        }

        if ($this->date) {
            return $this->date->format('Y-m-d H:i');
        }

        return null;
    }

    public function withOption(string $name, $value = null): Request
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function withTimezone(string $timezone): Request
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function atDate(Carbon $date): Request
    {
        $this->date = $date;

        return $this;
    }

    public function withKey(string $key): Request
    {
        $this->key = $key;

        return $this;
    }
}