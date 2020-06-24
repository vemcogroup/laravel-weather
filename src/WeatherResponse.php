<?php

namespace Vemcogroup\Weather;

class WeatherResponse
{
    protected $response;

    /**
     * Create WeatherResponse object
     *
     * @param object $response Entire JSON decoded response from API
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Get a JSON formatted object that is the entire response from Forecast.io.
     * This is useful if you do not wish to use any of the get methods provided
     * by this class or for accessing new or otherwise not otherwise accessible
     * data in the response.
     *
     * @return Object JSON-formatted object with the following properties defined:
     * latitude, longitude, timezone, offset, currently[minutely, hourly, daily]
     */
    public function getRawData()
    {
        return $this->response;
    }

    /**
     * The requested latitude.
     *
     * @return float The requested latitude
     */
    public function getLatitude(): ?float
    {
        $field = 'latitude';
        return $this->response->$field ?? null;
    }

    /**
     * The requested longitude.
     *
     * @return float The requested longitude
     */
    public function getLongitude(): ?float
    {
        $field = 'longitude';
        return $this->response->$field ?? null;
    }

    /**
     * The IANA timezone name for the requested location (e.g. America/New_York).
     * This is the timezone used for text forecast summaries and for determining
     * the exact start time of daily data points. (Developers are advised to rely
     * on local system settings rather than this value if at all possible: users
     * may deliberately set an unusual timezone, and furthermore are likely to
     * know what they actually want better than our timezone database does.)
     *
     * @return string The IANA timezone name for the requested location
     */
    public function getTimezone()
    {
        $field = 'timezone';
        return $this->response->$field ?? null;
    }

    /**
     * The current timezone offset in hours from GMT.
     *
     * @return string The current timezone offset in hours from GMT.
     */
    public function getOffset()
    {
        $field = 'offset';
        return $this->response->$field ?? null;
    }

    /**
     * Get number of ForecastDataPoint objects that exist within specified block
     *
     * @param string $type Type of data block
     *
     * @return int Returns number of ForecastDataPoint objects that exist within
     * specified block
     */
    public function getCount($type)
    {
        $response = $this->response;
        return empty($response->$type->data) ? false : count($response->$type->data);
    }

    /**
     * Get ForecastDataPoint object for current or specified time
     *
     * @return ForecastDataPoint ForecastDataPoint object for current or specified time
     */
    public function getCurrently()
    {
        return new ForecastDataPoint($this->response->currently);
    }

    /**
     * Get ForecastDataPoint object(s) desired within the specified block
     *
     * @param string $type Type of data block (
     * @param int $index Optional numeric index of desired data point in block
     * beginning with 0
     *
     * @return array|ForecastDataPoint|bool Returns an array of ForecastDataPoint
     * objects within the block OR a single ForecastDataPoint object for specified
     * block OR false if no applicable block
     */
    private function getBlock($type, $index = null)
    {
        if ($this->getCount($type)) {
            include_once 'ForecastDataPoint.php';
            $block_data = $this->response->$type->data;
            if (is_null($index)) {
                $points = [];
                foreach ($block_data as $point_data) {
                    $points[] = new ForecastDataPoint($point_data);
                }
                return $points;
            } elseif (is_int($index) && $this->getCount($type) > $index) {
                return new ForecastDataPoint($block_data[$index]);
            }
        }
        return false; // if no block, block but no data, or invalid index specified
    }

    /**
     * Get ForecastDataPoint object(s) desired within the minutely block, which is
     * weather conditions minute-by-minute for the next hour.
     *
     * @param int $index Optional numeric index of desired data point in block
     * beginning with 0
     *
     * @return array|ForecastDataPoint|bool Returns an array of ForecastDataPoint
     * objects within the block OR a single ForecastDataPoint object for specified
     * block OR false if no applicable block
     */
    public function getMinutely($index = null)
    {
        $type = 'minutely';
        return $this->getBlock($type, $index);
    }

    /**
     * Get ForecastDataPoint object(s) desired within the hourly block, which is
     * weather conditions hour-by-hour for the next two days.
     *
     * @param int $index Optional numeric index of desired data point in block
     * beginning with 0
     *
     * @return array|ForecastDataPoint|bool Returns an array of ForecastDataPoint
     * objects within the block OR a single ForecastDataPoint object for specified
     * block OR false if no applicable block
     */
    public function getHourly($index = null)
    {
        $type = 'hourly';
        return $this->getBlock($type, $index);
    }

    /**
     * Get ForecastDataPoint object(s) desired within the daily block, which is
     * weather conditions day-by-day for the next week.
     *
     * @param int $index Optional numeric index of desired data point in block
     * beginning with 0
     *
     * @return array|ForecastDataPoint|bool Returns an array of ForecastDataPoint
     * objects within the block OR a single ForecastDataPoint object for specified
     * block OR false if no applicable block
     */
    public function getDaily($index = null)
    {
        $type = 'daily';
        return $this->getBlock($type, $index);
    }
}
