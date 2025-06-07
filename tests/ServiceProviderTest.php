<?php

namespace TheAbhishekIN\FilamentLocation\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use TheAbhishekIN\FilamentLocation\FilamentLocationServiceProvider;
use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;
use TheAbhishekIN\FilamentLocation\Tables\Columns\LocationColumn;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_publishes_configuration()
    {
        $this->artisan('vendor:publish', [
            '--provider' => FilamentLocationServiceProvider::class,
            '--tag' => 'filament-location-config',
        ])->assertExitCode(0);
    }

    /** @test */
    public function it_publishes_assets()
    {
        $this->artisan('vendor:publish', [
            '--provider' => FilamentLocationServiceProvider::class,
            '--tag' => 'filament-location-assets',
        ])->assertExitCode(0);
    }

    /** @test */
    public function it_publishes_views()
    {
        $this->artisan('vendor:publish', [
            '--provider' => FilamentLocationServiceProvider::class,
            '--tag' => 'filament-location-views',
        ])->assertExitCode(0);
    }

    /** @test */
    public function it_loads_configuration()
    {
        // Test that configuration is loaded
        $this->assertNotNull(config('filament-location.google_maps_api_key'));
        $this->assertEquals(15, config('filament-location.default_zoom'));
        $this->assertEquals('400px', config('filament-location.map_height'));
        $this->assertEquals('standard', config('filament-location.map_type'));
        $this->assertTrue(config('filament-location.enable_high_accuracy'));
        $this->assertEquals(10000, config('filament-location.location_timeout'));
    }

    /** @test */
    public function it_loads_views()
    {
        // Test that views are loaded and accessible
        $this->assertTrue(View::exists('filament-location::forms.components.location-picker'));
        $this->assertTrue(View::exists('filament-location::tables.columns.location-column'));
    }

    /** @test */
    public function it_registers_components()
    {
        // Test that components can be instantiated
        $locationPicker = LocationPicker::make('test');
        $this->assertInstanceOf(LocationPicker::class, $locationPicker);

        $locationColumn = LocationColumn::make('test');
        $this->assertInstanceOf(LocationColumn::class, $locationColumn);
    }

    /** @test */
    public function it_loads_translations()
    {
        // Test that translations are loaded (if any)
        $this->assertIsString(__('filament-location::messages.select_location'));
    }

    /** @test */
    public function configuration_has_required_keys()
    {
        $config = config('filament-location');

        $requiredKeys = [
            'google_maps_api_key',
            'default_zoom',
            'map_height',
            'enable_street_view',
            'map_type',
            'location_accuracy',
            'location_timeout',
            'enable_high_accuracy',
            'map_controls'
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $config, "Configuration missing key: {$key}");
        }
    }

    /** @test */
    public function map_controls_configuration_is_valid()
    {
        $mapControls = config('filament-location.map_controls');

        $this->assertIsArray($mapControls);

        $expectedControls = [
            'zoom_control',
            'map_type_control',
            'scale_control',
            'street_view_control',
            'rotate_control',
            'fullscreen_control'
        ];

        foreach ($expectedControls as $control) {
            $this->assertArrayHasKey($control, $mapControls, "Map controls missing: {$control}");
            $this->assertIsBool($mapControls[$control], "Map control {$control} should be boolean");
        }
    }

    /** @test */
    public function it_can_override_configuration()
    {
        // Test configuration overrides
        Config::set('filament-location.default_zoom', 20);
        Config::set('filament-location.google_maps_api_key', 'custom-key');

        $this->assertEquals(20, config('filament-location.default_zoom'));
        $this->assertEquals('custom-key', config('filament-location.google_maps_api_key'));
    }

    /** @test */
    public function it_validates_map_type_values()
    {
        $validMapTypes = ['standard', 'satellite', 'hybrid', 'terrain'];
        $defaultMapType = config('filament-location.map_type');

        $this->assertContains($defaultMapType, $validMapTypes);
    }

    /** @test */
    public function location_timeout_is_numeric()
    {
        $timeout = config('filament-location.location_timeout');
        $this->assertIsNumeric($timeout);
        $this->assertGreaterThan(0, $timeout);
    }

    /** @test */
    public function default_zoom_is_valid()
    {
        $zoom = config('filament-location.default_zoom');
        $this->assertIsInt($zoom);
        $this->assertGreaterThanOrEqual(1, $zoom);
        $this->assertLessThanOrEqual(20, $zoom);
    }

    /** @test */
    public function enable_high_accuracy_is_boolean()
    {
        $enableHighAccuracy = config('filament-location.enable_high_accuracy');
        $this->assertIsBool($enableHighAccuracy);
    }

    /** @test */
    public function default_height_is_valid_css()
    {
        $height = config('filament-location.map_height');
        $this->assertIsString($height);

        // Should contain px, %, em, rem, or vh
        $this->assertMatchesRegularExpression('/\d+(px|%|em|rem|vh)$/', $height);
    }
}
