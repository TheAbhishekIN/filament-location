<?php

namespace TheAbhishekIN\FilamentLocation\Tests\Components;

use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;
use TheAbhishekIN\FilamentLocation\Tests\TestCase;

class LocationPickerTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $component = LocationPicker::make('location');

        $this->assertInstanceOf(LocationPicker::class, $component);
        $this->assertEquals('location', $component->getName());
    }

    /** @test */
    public function it_can_set_and_get_zoom_level()
    {
        $component = LocationPicker::make('location')->zoom(18);

        $this->assertEquals(18, $component->getZoom());
    }

    /** @test */
    public function it_can_set_and_get_height()
    {
        $component = LocationPicker::make('location')->height('500px');

        $this->assertEquals('500px', $component->getHeight());
    }

    /** @test */
    public function it_can_set_and_get_map_type()
    {
        $component = LocationPicker::make('location')->mapType('satellite');

        $this->assertEquals('satellite', $component->getMapType());
    }

    /** @test */
    public function it_can_configure_coordinates_display()
    {
        $component = LocationPicker::make('location')->showCoordinates(false);

        $this->assertFalse($component->getShowCoordinates());
    }

    /** @test */
    public function it_can_configure_map_display()
    {
        $component = LocationPicker::make('location')->showMap(false);

        $this->assertFalse($component->getShowMap());
    }

    /** @test */
    public function it_can_set_map_controls()
    {
        $controls = [
            'zoom_control' => false,
            'map_type_control' => true,
        ];

        $component = LocationPicker::make('location')->mapControls($controls);

        $this->assertEquals($controls, $component->getMapControls());
    }

    /** @test */
    public function it_uses_default_map_controls_when_none_specified()
    {
        $component = LocationPicker::make('location');

        $expectedControls = [
            'zoom_control' => true,
            'map_type_control' => true,
            'scale_control' => true,
            'street_view_control' => true,
            'rotate_control' => true,
            'fullscreen_control' => true,
        ];

        $this->assertEquals($expectedControls, $component->getMapControls());
    }

    /** @test */
    public function it_can_set_initial_location()
    {
        $location = ['latitude' => 26.9124, 'longitude' => 75.7873];

        $component = LocationPicker::make('location')->initialLocation($location);

        $this->assertEquals($location, $component->getInitialLocation());
    }

    /** @test */
    public function it_can_be_disabled()
    {
        $component = LocationPicker::make('location')->disabled();

        $this->assertTrue($component->isDisabled());
    }

    /** @test */
    public function it_can_have_helper_text()
    {
        $helperText = 'Click on the map to select a location';
        $component = LocationPicker::make('location')->helperText($helperText);

        $this->assertEquals($helperText, $component->getHelperText());
    }

    /** @test */
    public function it_handles_closure_based_configuration()
    {
        $component = LocationPicker::make('location')
            ->zoom(fn() => 18)
            ->height(fn() => '500px')
            ->mapType(fn() => 'satellite');

        $this->assertEquals(18, $component->getZoom());
        $this->assertEquals('500px', $component->getHeight());
        $this->assertEquals('satellite', $component->getMapType());
    }
}
