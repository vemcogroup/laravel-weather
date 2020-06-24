<?php

namespace Vemcogroup\Weather\Objects;

use Carbon\CarbonTimeZone;

class Forecast
{
    private $latitude;
    private $longitude;
    private $timezone;
    private $offset;
    private $currently;
    private $minutely;
    private $hourly;
    private $daily;
    private $cacheTTL;
    private $responseTime;

    /**
     * @param  array  $forecastData
     * @param  int|null  $cacheTTL
     * @param  int|null  $responseTime
     */
    public function __construct($forecastData, $cacheTTL = null, $responseTime = null)
    {
        if (isset($forecastData['latitude'])) {
            $this->latitude = $forecastData['latitude'];
        }
        if (isset($forecastData['longitude'])) {
            $this->longitude = $forecastData['longitude'];
        }
        if (isset($forecastData['timezone'])) {
            $this->timezone = new CarbonTimeZone($forecastData['timezone']);
        }
        if (isset($forecastData['offset'])) {
            $this->offset = $forecastData['offset'];
        }
        if (isset($forecastData['currently'])) {
            $this->currently = new DataPoint($forecastData['currently']);
        }
        if (isset($forecastData['minutely'])) {
            $this->minutely = new DataBlock($forecastData['minutely']);
        }
        if (isset($forecastData['hourly'])) {
            $this->hourly = new DataBlock($forecastData['hourly']);
        }
        if (isset($forecastData['daily'])) {
            $this->daily = new DataBlock($forecastData['daily']);
        }
        $this->cacheTTL = $cacheTTL;
        $this->responseTime = $responseTime;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getTimezone(): CarbonTimeZone
    {
        return $this->timezone;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getCurrently(): DataPoint
    {
        return $this->currently;
    }

    public function getMinutely(): DataBlock
    {
        return $this->minutely;
    }

    public function getHourly(): DataBlock
    {
        return $this->hourly;
    }

    public function getDaily(): DataBlock
    {
        return $this->daily;
    }

    public function getCacheTTL(): ?int
    {
        return $this->cacheTTL;
    }

    public function getResponseTime(): ?int
    {
        return $this->responseTime;
    }
}
