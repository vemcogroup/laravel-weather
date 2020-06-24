<?php

namespace Vemcogroup\Weather;

/**
 * ForecastTools: Data Point
 *
 * A ForecastDataPoint object represents the various weather phenomena occurring
 * at a specific instant of time, and has many varied methods. All of these
 * methods (except time) are optional, and will only be set if we have that
 * type of information for that location and time. Please note that minutely data
 * points are always aligned to the nearest minute boundary, hourly points to the
 * top of the hour, and daily points to midnight of that day.
 *
 * Data points in the daily data block (see below) are special: instead of
 * representing the weather phenomena at a given instant of time, they are an
 * aggregate point representing the weather phenomena that will occur over the
 * entire day. For precipitation fields, this aggregate is a maximum; for other
 * fields, it is an average.
 *
 * The following are not implemented as get functions due to lack of documentation:
 * All of the data oriented methods may have an associated error value defined,
 * representing our system’s confidence in its prediction. Such properties
 * represent standard deviations of the value of their associated property; small
 * error values therefore represent a strong confidence, while large error values
 * represent a weak confidence. These properties are omitted where the confidence
 * is not precisely known (though generally considered to be adequate).
 *
 * @package ForecastTools
 * @author  Charlie Gorichanaz <charlie@gorichanaz.com>
 * @license http://opensource.org/licenses/MIT The MIT License
 * @version 1.0
 * @link    http://github.com/CNG/ForecastTools
 * @example ../example.php
 */
class ForecastDataPoint
{
    private $pointData;

    /**
     * Create ForecastDataPoint object
     *
     * @param object $pointData JSON decoded data point from API response
     */
    public function __construct($pointData)
    {
        $this->pointData = $pointData;
    }

    public function getPointData()
    {
        return $this->pointData;
    }

    /**
     * The UNIX time (that is, seconds since midnight GMT on 1 Jan 1970) at which
     * this data point occurs.
     *
     * @return int|bool The UNIX time at which this data point occurs.
     */
    public function getTime()
    {
        $field = 'time';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A human-readable text summary of this data point.
     *
     * @return string|bool A human-readable text summary of this data point.
     */
    public function getSummary()
    {
        $field = 'summary';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A machine-readable text summary of this data point, suitable for selecting
     * an icon for display. If defined, this property will have one of the
     * following values: clear-day, clear-night, rain, snow, sleet, wind, fog,
     * cloudy, partly-cloudy-day, or partly-cloudy-night. (Developers should ensure
     * that a sensible default is defined, as additional values, such as hail,
     * thunderstorm, or tornado, may be defined in the future.)
     *
     * @return string|bool A machine-readable text summary of this data point
     */
    public function getIcon()
    {
        $field = 'icon';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): The UNIX time (that is, seconds since
     * midnight GMT on 1 Jan 1970) of sunrise and sunset on the given day. (If no
     * sunrise or sunset will occur on the given day, then the appropriate fields
     * will be undefined. This can occur during summer and winter in very high or
     * low latitudes.)
     *
     * @return int|bool sunriseTime
     */
    public function getSunriseTime()
    {
        $field = 'sunriseTime';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): The UNIX time (that is, seconds since
     * midnight GMT on 1 Jan 1970) of sunrise and sunset on the given day. (If no
     * sunrise or sunset will occur on the given day, then the appropriate fields
     * will be undefined. This can occur during summer and winter in very high or
     * low latitudes.)
     *
     * @return int|bool sunsetTime
     */
    public function getSunsetTime()
    {
        $field = 'sunsetTime';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A numerical value representing the average expected intensity (in inches of
     * liquid water per hour) of precipitation occurring at the given time
     * conditional on probability (that is, assuming any precipitation occurs at
     * all). A very rough guide is that a value of 0 in./hr. corresponds to no
     * precipitation, 0.002 in./hr. corresponds to very light precipitation, 0.017
     * in./hr. corresponds to light precipitation, 0.1 in./hr. corresponds to
     * moderate precipitation, and 0.4 in./hr. corresponds to heavy precipitation.
     *
     * @return float|bool precipIntensity
     */
    public function getPrecipIntensity()
    {
        $field = 'precipIntensity';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical values representing the
     * maximumum expected intensity of precipitation (and the UNIX time at which
     * it occurs) on the given day in inches of liquid water per hour.
     *
     * @return float|bool precipIntensityMax
     */
    public function getPrecipIntensityMax()
    {
        $field = 'precipIntensityMax';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical values representing the
     * maximumum expected intensity of precipitation (and the UNIX time at which
     * it occurs) on the given day in inches of liquid water per hour.
     *
     * @return int|bool precipIntensityMaxTime
     */
    public function getPrecipIntensityMaxTime()
    {
        $field = 'precipIntensityMaxTime';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A numerical value between 0 and 1 (inclusive) representing the probability
     * of precipitation occuring at the given time.
     *
     * @return float|bool precipProbability
     */
    public function getPrecipProbability()
    {
        $field = 'precipProbability';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A string representing the type of precipitation occurring at the given time.
     * If defined, this method will have one of the following values: rain, snow,
     * sleet (which applies to each of freezing rain, ice pellets, and “wintery
     * mix”), or hail. (If getPrecipIntensity() returns 0, then this method should
     * return false.)
     *
     * @return string|bool precipType
     */
    public function getPrecipType()
    {
        $field = 'precipType';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): the amount of snowfall accumulation
     * expected to occur on the given day. (If no accumulation is expected, this
     * method should return false.)
     *
     * @return float|bool precipAccumulation
     */
    public function getPrecipAccumulation()
    {
        $field = 'precipAccumulation';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (not defined on daily data points): A numerical value representing the
     * temperature at the given time in degrees Fahrenheit.
     *
     * @return float|bool temperature
     */
    public function getTemperature()
    {
        $field = 'temperature';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical value representing the
     * minimum temperatures on the given day in degrees Fahrenheit.
     *
     * @return float|bool temperatureMin
     */
    public function getTemperatureMin()
    {
        $field = 'temperatureMin';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical values representing the
     * minimum temperatures (and the UNIX times at which they
     * occur) on the given day in degrees Fahrenheit.
     *
     * @return int|bool temperatureMinTime
     */
    public function getTemperatureMinTime()
    {
        $field = 'temperatureMinTime';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical values representing the
     * maximumum temperatures (and the UNIX times at which they
     * occur) on the given day in degrees Fahrenheit.
     *
     * @return float|bool temperatureMax
     */
    public function getTemperatureMax()
    {
        $field = 'temperatureMax';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical values representing the
     * maximumum temperatures (and the UNIX times at which they
     * occur) on the given day in degrees Fahrenheit.
     *
     * @return int|bool temperatureMaxTime
     */
    public function getTemperatureMaxTime()
    {
        $field = 'temperatureMaxTime';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (not defined on daily data points): A numerical value representing the
     * apparent (or “feels like”) temperature at the given time in degrees
     * Fahrenheit.
     *
     * @return string|bool apparentTemperature
     */
    public function getApparentTemperature()
    {
        $field = 'apparentTemperature';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical value representing the
     * minimum apparent temperatures on the given day in degrees Fahrenheit.
     *
     * @return float|bool apparentTemperatureMin
     */
    public function getApparentTemperatureMin()
    {
        $field = 'apparentTemperatureMin';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical values representing the
     * minimum apparent temperatures (and the UNIX times at which they
     * occur) on the given day in degrees Fahrenheit.
     *
     * @return int|bool apparentTemperatureMinTime
     */
    public function getApparentTemperatureMinTime()
    {
        $field = 'apparentTemperatureMinTime';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical values representing the
     * maximumum apparent temperatures (and the UNIX times at which they
     * occur) on the given day in degrees Fahrenheit.
     *
     * @return float|bool apparentTemperatureMax
     */
    public function getApparentTemperatureMax()
    {
        $field = 'apparentTemperatureMax';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * (only defined on daily data points): numerical values representing the
     * maximumum apparent temperatures (and the UNIX times at which they
     * occur) on the given day in degrees Fahrenheit.
     *
     * @return int|bool apparentTemperatureMaxTime
     */
    public function getApparentTemperatureMaxTime()
    {
        $field = 'apparentTemperatureMaxTime';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A numerical value representing the dew point at the given time in degrees
     * Fahrenheit.
     *
     * @return float|bool dewPoint
     */
    public function getDewPoint()
    {
        $field = 'dewPoint';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     *  A numerical value representing the wind speed in miles per hour.
     *
     * @return float|bool windSpeed
     */
    public function getWindSpeed()
    {
        $field = 'windSpeed';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A numerical value representing the direction that the wind is coming from
     * in degrees, with true north at 0° and progressing clockwise. (If
     * getWindSpeed is zero, then this value will not be defined.)
     *
     * @return string|bool windBearing
     */
    public function getWindBearing()
    {
        $field = 'windBearing';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A numerical value between 0 and 1 (inclusive) representing the percentage
     * of sky occluded by clouds. A value of 0 corresponds to clear sky, 0.4 to
     * scattered clouds, 0.75 to broken cloud cover, and 1 to completely overcast
     * skies.
     *
     * @return float|bool cloudCover
     */
    public function getCloudCover()
    {
        $field = 'cloudCover';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A numerical value between 0 and 1 (inclusive) representing the relative
     * humidity.
     *
     * @return float|bool humidity
     */
    public function getHumidity()
    {
        $field = 'humidity';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A numerical value representing the sea-level air pressure in millibars.
     *
     * @return float|bool pressure
     */
    public function getPressure()
    {
        $field = 'pressure';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A numerical value representing the average visibility in miles, capped
     * at 10 miles.
     *
     * @return float|bool visibility
     */
    public function getVisibility()
    {
        $field = 'visibility';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }

    /**
     * A numerical value representing the columnar density of total atmospheric
     * ozone at the given time in Dobson units.
     *
     * @return float|bool ozone
     */
    public function getOzone()
    {
        $field = 'ozone';
        return empty($this->pointData->$field) ? false : $this->pointData->$field;
    }
}
