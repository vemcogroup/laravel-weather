<?php

namespace Vemcogroup\Weather\Tests;

use Illuminate\Support\Facades\Cache;
use Vemcogroup\Weather\Request;
use Vemcogroup\Weather\Exceptions\WeatherException;

class RequestTest extends TestCase
{
    /**
     * @test
     */
    public function itCachesFailedGeocodeLookupToPreventInfiniteRetry(): void
    {
        $address = 'not-found-address';
        $cacheKey = md5('laravel-weather-geocode-' . $address);

        Cache::forget($cacheKey);
        $this->addMockHandler(200, $this->getFile('geocoder/zero-coordinates.json'));

        $request = new Request($address);

        try {
            $request->lookupGeocode();
            $this->fail('Expected geocode lookup to fail.');
        } catch (WeatherException $exception) {
            $this->assertStringContainsString('Invalid address', $exception->getMessage());
        }

        $this->assertSame(Request::GEOCODE_LOOKUP_FAILED_CACHE_VALUE, Cache::get($cacheKey));

        $this->expectException(WeatherException::class);
        $this->expectExceptionMessage('cached geocode lookup failed');

        $request->lookupGeocode();
    }

    /**
     * @test
     */
    public function itRetriesGeocodeLookupWhenCachedValueIsCorrupt(): void
    {
        $address = 'cached-corrupt-address';
        $cacheKey = md5('laravel-weather-geocode-' . $address);

        Cache::put($cacheKey, ['invalid' => 'value'], now()->addMinutes(10));
        $this->addMockHandler(200, $this->getFile('geocoder.json'));

        $request = new Request($address);
        $request->lookupGeocode();

        $this->assertSame(37.4224764, $request->getLatitude());
        $this->assertSame(-122.0842499, $request->getLongitude());
    }
}
