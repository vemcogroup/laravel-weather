<?php

namespace Vemcogroup\Weather;

use Illuminate\Support\ServiceProvider;

class WeatherServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/weather.php' => config_path('weather.php'),
        ], 'config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/weather.php', 'weather'
        );

        $this->app->bind(WeatherProvider::class);
    }
}
