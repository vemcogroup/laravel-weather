<?php

namespace Vemcogroup\Weather;

use Carbon\CarbonTimeZone;
use Vemcogroup\Weather\Objects\DataPoint;
use Vemcogroup\Weather\Objects\DataBlock;

class Response
{
    private $daily;
    private $hourly;
    private $timezone;
    private $minutely;
    private $currently;
    private $offset = 0;
    private $latitude = 0.0;
    private $longitude = 0.0;

    public function __construct($data)
    {
        if (is_array($data)) {
            $data = (object) $data;
        }

        if (isset($data->latitude)) {
            $this->latitude = $data->latitude;
        }
        if (isset($data->longitude)) {
            $this->longitude = $data->longitude;
        }
        if (isset($data->timezone)) {
            $this->timezone = new CarbonTimeZone($data->timezone);
        }
        if (isset($data->offset)) {
            $this->offset = $data->offset;
        }
        if (isset($data->currently)) {
            $this->currently = new DataPoint($data->currently);
        }
        if (isset($data->minutely)) {
            $this->minutely = new DataBlock($data->minutely);
        }
        if (isset($data->hourly)) {
            $this->hourly = new DataBlock($data->hourly);
        }
        if (isset($data->daily)) {
            $this->daily = new DataBlock($data->daily);
        }
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getTimezone(): ?CarbonTimeZone
    {
        return $this->timezone;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getCurrently(): ?DataPoint
    {
        return $this->currently;
    }

    public function getMinutely(): ?DataBlock
    {
        return $this->minutely;
    }

    public function getHourly(): ?DataBlock
    {
        return $this->hourly;
    }

    public function getDaily(): ?DataBlock
    {
        return $this->daily;
    }
}
