<?php

namespace Vemcogroup\Weather\Exceptions;

use Exception;

class WeatherException extends Exception
{
    public static function noApiKey(): self
    {
        return new static('Missing WEATHER_API_KEY, please add it in .env', 1001);
    }

    public static function noProvider(): self
    {
        return new static('Missing WEATHER_PROVIDER, please add it in .env', 1002);
    }

    public static function noUrl(): self
    {
        return new static('No url provided', 1003);
    }

    public static function wrongProvider(): self
    {
        return new static('Wrong WEATHER_PROVIDER, please check README for valid providers', 1004);
    }

    public static function communicationError($message): self
    {
        return new static('Error in communication with provider: ' . $message, 1005);
    }

    public static function invalidAddress($address, $message): self
    {
        return new static('Invalid address:  ' . $address . ' error: ' . $message, 1006);
    }
}
