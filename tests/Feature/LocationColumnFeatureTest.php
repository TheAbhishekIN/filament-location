<?php

use Illuminate\Database\Eloquent\Model;
use TheAbhishekIN\FilamentLocation\Tables\Columns\LocationColumn;

it('can be instantiated', function () {
    $column = LocationColumn::make('location');

    expect($column)->toBeInstanceOf(LocationColumn::class);
    expect($column->getName())->toBe('location');
});

it('can set latitude and longitude fields', function () {
    $column = LocationColumn::make('location')
        ->latitude('lat_field')
        ->longitude('lng_field');

    expect($column->getLatitudeField())->toBe('lat_field');
    expect($column->getLongitudeField())->toBe('lng_field');
});

it('can set zoom level', function () {
    $column = LocationColumn::make('location')->zoom(18);

    expect($column->getZoom())->toBe(18);
});

it('can set height', function () {
    $column = LocationColumn::make('location')->height('500px');

    expect($column->getHeight())->toBe('500px');
});

it('can set map type', function () {
    $column = LocationColumn::make('location')->mapType('satellite');

    expect($column->getMapType())->toBe('satellite');
});

it('can configure tooltip', function () {
    $column = LocationColumn::make('location')
        ->showTooltip(false)
        ->tooltipText('Custom tooltip');

    expect($column->getShowTooltip())->toBeFalse();
    expect($column->getTooltipText())->toBe('Custom tooltip');
});

it('can set icon properties', function () {
    $column = LocationColumn::make('location')
        ->iconSize('lg')
        ->iconColor('success');

    expect($column->getIconSize())->toBe('lg');
    expect($column->getIconColor())->toBe('success');
});

it('can set custom icon', function () {
    $customIcon = '<path d="custom-icon-path"/>';
    $column = LocationColumn::make('location')->customIcon($customIcon);

    expect($column->getCustomIcon())->toBe($customIcon);
    expect($column->getUseCustomIcon())->toBeTrue();
});

it('can set icon type', function () {
    $column = LocationColumn::make('location')->iconType('check-in');

    expect($column->getUseCustomIcon())->toBeTrue();
    expect($column->getCustomIcon())->not->toBeNull();
});

it('can set title', function () {
    $column = LocationColumn::make('location')->title('Location Details');

    expect($column->getTitle())->toBe('Location Details');
});

it('can set custom location getters', function () {
    $column = LocationColumn::make('location')
        ->getLatitudeUsing(fn($record) => $record->custom_lat)
        ->getLongitudeUsing(fn($record) => $record->custom_lng);

    expect($column->getLatitudeUsing)->not->toBeNull();
    expect($column->getLongitudeUsing)->not->toBeNull();
});

it('extracts location data from record', function () {
    $record = new class extends Model {
        public $latitude = 26.9124;
        public $longitude = 75.7873;
    };

    $column = LocationColumn::make('location')
        ->latitude('latitude')
        ->longitude('longitude');

    $locationData = $column->getLocationData($record);

    expect($locationData['latitude'])->toBe(26.9124);
    expect($locationData['longitude'])->toBe(75.7873);
    expect($locationData['hasLocation'])->toBeTrue();
});

it('handles missing location data', function () {
    $record = new class extends Model {
        public $latitude = null;
        public $longitude = null;
    };

    $column = LocationColumn::make('location')
        ->latitude('latitude')
        ->longitude('longitude');

    $locationData = $column->getLocationData($record);

    expect($locationData['latitude'])->toBeNull();
    expect($locationData['longitude'])->toBeNull();
    expect($locationData['hasLocation'])->toBeFalse();
});

it('uses custom getters for location data', function () {
    $record = new class extends Model {
        public $custom_lat = 26.9124;
        public $custom_lng = 75.7873;
    };

    $column = LocationColumn::make('location')
        ->getLatitudeUsing(fn($record) => $record->custom_lat)
        ->getLongitudeUsing(fn($record) => $record->custom_lng);

    $locationData = $column->getLocationData($record);

    expect($locationData['latitude'])->toBe(26.9124);
    expect($locationData['longitude'])->toBe(75.7873);
    expect($locationData['hasLocation'])->toBeTrue();
});

it('returns default map controls', function () {
    $column = LocationColumn::make('location');

    $controls = $column->getMapControls();

    $expectedControls = [
        'zoom_control' => true,
        'map_type_control' => true,
        'scale_control' => true,
        'street_view_control' => true,
        'rotate_control' => true,
        'fullscreen_control' => true,
    ];

    expect($controls)->toBe($expectedControls);
});

it('can override map controls', function () {
    $customControls = [
        'zoom_control' => false,
        'map_type_control' => true,
    ];

    $column = LocationColumn::make('location')->mapControls($customControls);

    expect($column->getMapControls())->toBe($customControls);
});

it('detects if record has location', function () {
    $recordWithLocation = new class extends Model {
        public $latitude = 26.9124;
        public $longitude = 75.7873;
    };

    $recordWithoutLocation = new class extends Model {
        public $latitude = null;
        public $longitude = null;
    };

    $column = LocationColumn::make('location')
        ->latitude('latitude')
        ->longitude('longitude');

    expect($column->hasLocation($recordWithLocation))->toBeTrue();
    expect($column->hasLocation($recordWithoutLocation))->toBeFalse();
});

it('handles different icon types', function () {
    $checkInColumn = LocationColumn::make('check_in')->iconType('check-in');
    $checkOutColumn = LocationColumn::make('check_out')->iconType('check-out');
    $homeColumn = LocationColumn::make('home')->iconType('home');
    $officeColumn = LocationColumn::make('office')->iconType('office');
    $travelColumn = LocationColumn::make('travel')->iconType('travel');

    expect($checkInColumn->getUseCustomIcon())->toBeTrue();
    expect($checkOutColumn->getUseCustomIcon())->toBeTrue();
    expect($homeColumn->getUseCustomIcon())->toBeTrue();
    expect($officeColumn->getUseCustomIcon())->toBeTrue();
    expect($travelColumn->getUseCustomIcon())->toBeTrue();

    // Each should have different icon content
    expect($checkInColumn->getCustomIcon())->not->toBe($checkOutColumn->getCustomIcon());
    expect($homeColumn->getCustomIcon())->not->toBe($officeColumn->getCustomIcon());
});

it('can be searchable', function () {
    $column = LocationColumn::make('location')->searchable();

    expect($column->isSearchable())->toBeTrue();
});

it('can be sortable', function () {
    $column = LocationColumn::make('location')->sortable();

    expect($column->isSortable())->toBeTrue();
});
