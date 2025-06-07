<?php

namespace TheAbhishekIN\FilamentLocation;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class FilamentLocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-location.php',
            'filament-location'
        );
    }

    public function boot(): void
    {
        // Register assets
        FilamentAsset::register([
            Css::make('filament-location-styles', __DIR__ . '/../resources/dist/filament-location.css'),
            Js::make('filament-location-scripts', __DIR__ . '/../resources/dist/filament-location.js'),
        ], 'theabhishekin/filament-location');

        // Register script data with proper variable name
        FilamentAsset::registerScriptData([
            'filamentLocationConfig' => [
                'googleMapsApiKey' => config('filament-location.google_maps_api_key'),
                'defaultZoom' => config('filament-location.default_zoom', 15),
                'mapHeight' => config('filament-location.map_height', '400px'),
                'enableHighAccuracy' => config('filament-location.enable_high_accuracy', true),
                'locationTimeout' => config('filament-location.location_timeout', 10000),
            ],
        ], 'theabhishekin/filament-location');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-location');

        // Publishing
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/filament-location.php' => config_path('filament-location.php'),
            ], 'filament-location-config');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'filament-location-migrations');

            // Publish views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-location'),
            ], 'filament-location-views');

            // Publish assets
            $this->publishes([
                __DIR__ . '/../resources/dist' => public_path('vendor/filament-location'),
            ], 'filament-location-assets');
        }
    }
}
