<?php

namespace Vemcogroup\Weather\Objects;

use Carbon\Carbon;

class Precipitation
{
    private $intensity;
    private $maxIntensity;
    private $maxIntensityTime;
    private $probability;
    private $type;
    private $accumulation;

    public function __construct($intensity, $maxIntensity, $maxIntensityTime, $probability, $type, $accumulation)
    {
        $this->intensity = $intensity;
        $this->maxIntensity = $maxIntensity;
        if (!is_null($maxIntensityTime)) {
            $this->maxIntensityTime = new Carbon($maxIntensityTime);
        }
        $this->probability = $probability;
        $this->type = $type;
        $this->accumulation = $accumulation;
    }

    public function getIntensity(): float
    {
        return $this->intensity;
    }

    public function getMaxIntensity(): float
    {
        return $this->maxIntensity;
    }

    public function getMaxIntensityTime(): Carbon
    {
        return $this->maxIntensityTime;
    }

    public function getProbability(): float
    {
        return $this->probability;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAccumulation(): float
    {
        return $this->accumulation;
    }
}
