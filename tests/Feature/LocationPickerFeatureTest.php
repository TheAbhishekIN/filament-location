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

it('can be disabled', function () {
    $component = LocationPicker::make('location')->disabled();

    expect($component->isDisabled())->toBeTrue();
});

it('can have helper text', function () {
    $helperText = 'Click on the map to select a location';
    $component = LocationPicker::make('location')->helperText($helperText);

    expect($component->getHelperText())->toBe($helperText);
});

it('handles closure based configuration', function () {
    $component = LocationPicker::make('location')
        ->zoom(fn() => 18)
        ->height(fn() => '500px')
        ->mapType(fn() => 'satellite');

    expect($component->getZoom())->toBe(18);
    expect($component->getHeight())->toBe('500px');
    expect($component->getMapType())->toBe('satellite');
});
