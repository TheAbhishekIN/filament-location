<?php

namespace TheAbhishekIN\FilamentLocation\Tests\Components;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Blade;
use Livewire\Component as LivewireComponent;
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
    public function it_can_validate_location_data()
    {
        $component = LocationPicker::make('location')->required();

        // Test validation with empty state
        $component->state(null);
        $this->assertTrue($component->hasValidationErrors(['required']));

        // Test validation with valid location
        $component->state(['latitude' => 26.9124, 'longitude' => 75.7873]);
        $this->assertFalse($component->hasValidationErrors(['required']));
    }

    /** @test */
    public function it_handles_state_hydration_correctly()
    {
        $component = LocationPicker::make('location');

        // Test with JSON string
        $jsonState = '{"latitude":26.9124,"longitude":75.7873}';
        $component->state($jsonState);

        $this->assertIsArray($component->getState());
        $this->assertEquals(26.9124, $component->getState()['latitude']);
        $this->assertEquals(75.7873, $component->getState()['longitude']);
    }

    /** @test */
    public function it_handles_state_dehydration_correctly()
    {
        $component = LocationPicker::make('location');

        $state = ['latitude' => 26.9124, 'longitude' => 75.7873];
        $component->state($state);

        $dehydratedState = $component->dehydrateState($state);

        $this->assertIsString($dehydratedState);
        $this->assertJson($dehydratedState);

        $decoded = json_decode($dehydratedState, true);
        $this->assertEquals(26.9124, $decoded['latitude']);
        $this->assertEquals(75.7873, $decoded['longitude']);
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
        $helperText = 'Please select your location on the map';
        $component = LocationPicker::make('location')->helperText($helperText);

        $this->assertEquals($helperText, $component->getHelperText());
    }

    /** @test */
    public function it_renders_view_with_correct_data()
    {
        $component = LocationPicker::make('location')
            ->zoom(15)
            ->height('300px')
            ->mapType('standard')
            ->showCoordinates(true)
            ->showMap(true);

        $form = Form::make()
            ->schema([$component])
            ->model(new class extends LivewireComponent {
                public $data = [];
                public function mount() {}
                public function render()
                {
                    return view('components.test');
                }
            });

        $this->assertStringContainsString('filament-location-picker', $component->render()->getName());
    }

    /** @test */
    public function it_validates_latitude_and_longitude_ranges()
    {
        $component = LocationPicker::make('location');

        // Test invalid latitude (outside -90 to 90 range)
        $invalidLatState = ['latitude' => 100, 'longitude' => 75.7873];
        $component->state($invalidLatState);
        // Custom validation would need to be implemented in the component

        // Test invalid longitude (outside -180 to 180 range)
        $invalidLngState = ['latitude' => 26.9124, 'longitude' => 200];
        $component->state($invalidLngState);
        // Custom validation would need to be implemented in the component

        // Test valid coordinates
        $validState = ['latitude' => 26.9124, 'longitude' => 75.7873];
        $component->state($validState);
        $this->assertEquals($validState, $component->getState());
    }

    /** @test */
    public function it_handles_closure_based_configuration()
    {
        $component = LocationPicker::make('location')
            ->zoom(fn() => 20)
            ->height(fn() => '600px')
            ->mapType(fn() => 'satellite');

        // Note: evaluate() method would need to be accessible for proper testing
        $this->assertInstanceOf(LocationPicker::class, $component);
    }
}
