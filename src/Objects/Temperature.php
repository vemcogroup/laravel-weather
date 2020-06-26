<?php

namespace Vemcogroup\Weather\Objects;

use Carbon\Carbon;

class Temperature
{
    private $min;
    private $max;
    private $current;
    private $minTime;
    private $maxTime;

    public function __construct($current, $min, $minTime, $max, $maxTime)
    {
        $this->current = $current;
        $this->min = $min;
        $this->minTime = $minTime ? Carbon::parse($minTime) : null;
        $this->max = $max;
        $this->maxTime = $maxTime ? Carbon::parse($maxTime) : null;
    }

    public function getCurrent(): float
    {
        return $this->current;
    }

    public function getMin(): float
    {
        return $this->min;
    }

    public function getMinTime(): Carbon
    {
        return $this->minTime;
    }

    public function getMax(): float
    {
        return $this->max;
    }

    public function getMaxTime(): Carbon
    {
        return $this->maxTime;
    }
}
