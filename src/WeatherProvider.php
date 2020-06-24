<?php

namespace Vemcogroup\Weather;

use Exception;
use Vemcogroup\Weather\Objects\Forecast;
use Vemcogroup\Weather\Providers\Darksky;
use Vemcogroup\Weather\Providers\Provider;
use Vemcogroup\Weather\Providers\Weatherstack;
use Vemcogroup\Weather\Exceptions\WeatherException;

class WeatherProvider
{
    /**
     * @var Provider $provider
     */
    private $provider;
    private static $providers = [
        'darksky' => Darksky::class,
        'weatherstack' => Weatherstack::class,
    ];

    public function __construct()
    {
        try {
            if (!$name = config('weather.provider')) {
                throw WeatherException::noProvider();
            }

            if ($name === null || !isset(self::$providers[$name])) {
                throw WeatherException::wrongProvider();
            }

            $this->provider = new self::$providers[$name]();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieve WeatherResponse objects for each set of location and time.
     *
     * You can call the function with either the values for one API request or an
     * array or arrays, with one subarray for each API request. Therefore making
     * one API request can be done by passing in latitude, longitude, etc., or by
     * passing an array containing one array. Making multiple simultaneous API
     * requests must be done by passing an array of arrays. The return value will
     * typically be either an array of WeatherResponse objects or a single such
     * object. If you pass in an array, you will get an array back.
     *
     * For each request, either a WeatherResponse object or false will be
     * returned, so you must check for false. This can indicate the data is not
     * available for that request.
     *
     * If invalid parameters are passed, this can throw a ForecastException. If
     * other errors occur, such as a problem making the request or data not being
     * available, the response will generally just be false. Some errors are
     * logged with trigger_error to the same location as PHP warnings and notices.
     * You must therefore write code in a way that will handle false values. You
     * probably do not need to handle the ForecastException unless your production
     * code might result in variable parameters or formats.
     *
     * @param  mixed  $requests  Pass either of the following:
     * <ol>
     *   <li>
     *     One parameter that is an array of one or more associative arrays like
     *     <pre>
     *       array(
     *         'latitude'  => float,
     *         'longitude' => float,
     *         'time'      => int,
     *         'units'     => string,
     *         'exclude'   => string,
     *         'extend'    => string,
     *         'callback'  => string,
     *       )
     *     </pre>
     *     with only the latitiude and longitude required
     *   </li>
     *   <li>
     *     Two to seven parameters in this order: latitude float, longitude float,
     *     time int, units string, exclude string, extend string, callback string
     *   </li>
     * </ul>
     *
     * @return array|WeatherResponse|bool If array passed in, returns array of
     * ForecastIOConditions objects or false vales. Otherwise returns a single
     * WeatherResponse object or false value.
     */

    public function getData($requests)
    {
        $conditions = [];
        foreach ($this->provider->getWeather($requests) as $key => $result) {
            if ($result !== false && $result !== null) {
                $conditions[$key] = new WeatherResponse($result);
            } else {
                $conditions[$key] = false;
            }
        }

        return $conditions;
    }

    public function getForecast($geocode, $parameters = null): Forecast
    {
        return $this->provider->getForecast($geocode, $parameters);
    }
}
