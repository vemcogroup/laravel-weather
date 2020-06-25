<?php

namespace Vemcogroup\Weather\Exceptions;

use Exception;

class WeatherException extends Exception
{
    public static function noApiKey(): self
    {
        return new static('Missing WEATHER_API_KEY, please add it in .env');
    }

    public static function noProvider(): self
    {
        return new static('Missing WEATHER_PROVIDER, please add it in .env');
    }

    public static function noUrl(): self
    {
        return new static('No url provided, please set url');
    }

    public static function wrongProvider(): self
    {
        return new static('Wrong WEATHER_PROVIDER, please check README for valid providers');
    }

    public static function communicationError($message): self
    {
        return new static('Error in communication with POEditor: ' . $message);
    }

    public static function invalidAddress($address, $message): self
    {
        return new static('Invalid address:  ' . $address . ' error: ' . $message);
    }
}
