<?php

namespace Vemcogroup\Weather\Objects;

use DateTime;
use Carbon\Carbon;

class Temperature
{
    private $current;
    private $min;
    private $minTime;
    private $max;
    private $maxTime;

    public function __construct($current, $min, $minTime, $max, $maxTime)
    {
        $this->current = $current;
        $this->min = $min;
        if (!is_null($minTime)) {
            $this->minTime = Carbon::parse($minTime);
        }
        $this->max = $max;
        if (!is_null($maxTime)) {
            $this->maxTime = Carbon::parse($maxTime);
        }
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
