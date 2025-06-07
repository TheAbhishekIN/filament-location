<?php

namespace TheAbhishekIN\FilamentLocation\Tests;

use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use TheAbhishekIN\FilamentLocation\FilamentLocationServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'TheAbhishekIN\\FilamentLocation\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            FilamentLocationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up Filament Location configuration
        config()->set('filament-location.google_maps_api_key', 'test-api-key');
        config()->set('filament-location.default_zoom', 15);
        config()->set('filament-location.default_height', '400px');
        config()->set('filament-location.default_map_type', 'standard');
        config()->set('filament-location.enable_high_accuracy', true);
        config()->set('filament-location.location_timeout', 10000);
        config()->set('filament-location.map_controls', [
            'zoom_control' => true,
            'map_type_control' => true,
            'scale_control' => true,
            'street_view_control' => true,
            'rotate_control' => true,
            'fullscreen_control' => true,
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
