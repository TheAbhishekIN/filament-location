<?php

use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;

it('can be instantiated', function () {
    $component = LocationPicker::make('location');

    expect($component)->toBeInstanceOf(LocationPicker::class);
    expect($component->getName())->toBe('location');
});

it('can set and get zoom level', function () {
    $component = LocationPicker::make('location')->zoom(18);

    expect($component->getZoom())->toBe(18);
});

it('can set and get height', function () {
    $component = LocationPicker::make('location')->height('500px');

    expect($component->getHeight())->toBe('500px');
});

it('can set and get map type', function () {
    $component = LocationPicker::make('location')->mapType('satellite');

    expect($component->getMapType())->toBe('satellite');
});

it('can configure coordinates display', function () {
    $component = LocationPicker::make('location')->showCoordinates(false);

    expect($component->getShowCoordinates())->toBeFalse();
});

it('can configure map display', function () {
    $component = LocationPicker::make('location')->showMap(false);

    expect($component->getShowMap())->toBeFalse();
});

it('can set map controls', function () {
    $controls = [
        'zoom_control' => false,
        'map_type_control' => true,
    ];

    $component = LocationPicker::make('location')->mapControls($controls);

    expect($component->getMapControls())->toBe($controls);
});

it('uses default map controls when none specified', function () {
    $component = LocationPicker::make('location');

    $expectedControls = [
        'zoom_control' => true,
        'map_type_control' => true,
        'scale_control' => true,
        'street_view_control' => true,
        'rotate_control' => true,
        'fullscreen_control' => true,
    ];

    expect($component->getMapControls())->toBe($expectedControls);
});

it('can set initial location', function () {
    $location = ['latitude' => 26.9124, 'longitude' => 75.7873];

    $component = LocationPicker::make('location')->initialLocation($location);

    expect($component->getInitialLocation())->toBe($location);
});

it('handles state hydration correctly', function () {
    $component = LocationPicker::make('location');

    // Test with JSON string
    $jsonState = '{"latitude":26.9124,"longitude":75.7873}';
    $component->state($jsonState);

    expect($component->getState())->toBeArray();
    expect($component->getState()['latitude'])->toBe(26.9124);
    expect($component->getState()['longitude'])->toBe(75.7873);
});

it('handles state dehydration correctly', function () {
    $component = LocationPicker::make('location');

    $state = ['latitude' => 26.9124, 'longitude' => 75.7873];
    $component->state($state);

    $dehydratedState = $component->dehydrateState($state);

    expect($dehydratedState)->toBeString();
    expect($dehydratedState)->toBeJson();

    $decoded = json_decode($dehydratedState, true);
    expect($decoded['latitude'])->toBe(26.9124);
    expect($decoded['longitude'])->toBe(75.7873);
});

it('can be disabled', function () {
    $component = LocationPicker::make('location')->disabled();

    expect($component->isDisabled())->toBeTrue();
});

it('can have helper text', function () {
    $helperText = 'Please select your location on the map';
    $component = LocationPicker::make('location')->helperText($helperText);

    expect($component->getHelperText())->toBe($helperText);
});

it('validates latitude and longitude ranges', function () {
    $component = LocationPicker::make('location');

    // Test valid coordinates
    $validState = ['latitude' => 26.9124, 'longitude' => 75.7873];
    $component->state($validState);
    expect($component->getState())->toBe($validState);

    // Test coordinates boundaries
    $validBoundaries = [
        ['latitude' => 90, 'longitude' => 180],
        ['latitude' => -90, 'longitude' => -180],
        ['latitude' => 0, 'longitude' => 0],
    ];

    foreach ($validBoundaries as $boundary) {
        $component->state($boundary);
        expect($component->getState())->toBe($boundary);
    }
});

it('handles closure based configuration', function () {
    $component = LocationPicker::make('location')
        ->zoom(fn() => 20)
        ->height(fn() => '600px')
        ->mapType(fn() => 'satellite');

    expect($component)->toBeInstanceOf(LocationPicker::class);
});
