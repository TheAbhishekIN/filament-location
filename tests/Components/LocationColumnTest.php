<?php

namespace TheAbhishekIN\FilamentLocation\Tests\Components;

use Illuminate\Database\Eloquent\Model;
use TheAbhishekIN\FilamentLocation\Tables\Columns\LocationColumn;
use TheAbhishekIN\FilamentLocation\Tests\TestCase;

class LocationColumnTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $column = LocationColumn::make('location');

        $this->assertInstanceOf(LocationColumn::class, $column);
        $this->assertEquals('location', $column->getName());
    }

    /** @test */
    public function it_can_set_latitude_and_longitude_fields()
    {
        $column = LocationColumn::make('location')
            ->latitude('lat_field')
            ->longitude('lng_field');

        $this->assertEquals('lat_field', $column->getLatitudeField());
        $this->assertEquals('lng_field', $column->getLongitudeField());
    }

    /** @test */
    public function it_can_set_zoom_level()
    {
        $column = LocationColumn::make('location')->zoom(18);

        $this->assertEquals(18, $column->getZoom());
    }

    /** @test */
    public function it_can_set_height()
    {
        $column = LocationColumn::make('location')->height('500px');

        $this->assertEquals('500px', $column->getHeight());
    }

    /** @test */
    public function it_can_set_map_type()
    {
        $column = LocationColumn::make('location')->mapType('satellite');

        $this->assertEquals('satellite', $column->getMapType());
    }

    /** @test */
    public function it_can_configure_tooltip()
    {
        $column = LocationColumn::make('location')
            ->showTooltip(false)
            ->tooltipText('Custom tooltip');

        $this->assertFalse($column->getShowTooltip());
        $this->assertEquals('Custom tooltip', $column->getTooltipText());
    }

    /** @test */
    public function it_can_set_icon_properties()
    {
        $column = LocationColumn::make('location')
            ->iconSize('lg')
            ->iconColor('success');

        $this->assertEquals('lg', $column->getIconSize());
        $this->assertEquals('success', $column->getIconColor());
    }

    /** @test */
    public function it_can_set_custom_icon()
    {
        $customIcon = '<path d="custom-icon-path"/>';
        $column = LocationColumn::make('location')->customIcon($customIcon);

        $this->assertEquals($customIcon, $column->getCustomIcon());
        $this->assertTrue($column->getUseCustomIcon());
    }

    /** @test */
    public function it_can_set_icon_type()
    {
        $column = LocationColumn::make('location')->iconType('check-in');

        $this->assertTrue($column->getUseCustomIcon());
        $this->assertNotNull($column->getCustomIcon());
    }

    /** @test */
    public function it_can_set_title()
    {
        $column = LocationColumn::make('location')->title('Location Details');

        $this->assertEquals('Location Details', $column->getTitle());
    }

    /** @test */
    public function it_can_set_custom_location_getters()
    {
        $column = LocationColumn::make('location')
            ->getLatitudeUsing(fn($record) => $record->custom_lat)
            ->getLongitudeUsing(fn($record) => $record->custom_lng);

        $this->assertNotNull($column->getLatitudeUsing);
        $this->assertNotNull($column->getLongitudeUsing);
    }

    /** @test */
    public function it_extracts_location_data_from_record()
    {
        $record = new class extends Model {
            protected $fillable = ['latitude', 'longitude'];

            public function __construct()
            {
                parent::__construct();
                $this->latitude = 26.9124;
                $this->longitude = 75.7873;
            }
        };

        $column = LocationColumn::make('location')
            ->latitude('latitude')
            ->longitude('longitude');

        $locationData = $column->getLocationData($record);

        $this->assertEquals(26.9124, $locationData['latitude']);
        $this->assertEquals(75.7873, $locationData['longitude']);
        $this->assertTrue($locationData['hasLocation']);
    }

    /** @test */
    public function it_handles_missing_location_data()
    {
        $record = new class extends Model {
            protected $fillable = ['latitude', 'longitude'];

            public function __construct()
            {
                parent::__construct();
                $this->latitude = null;
                $this->longitude = null;
            }
        };

        $column = LocationColumn::make('location')
            ->latitude('latitude')
            ->longitude('longitude');

        $locationData = $column->getLocationData($record);

        $this->assertNull($locationData['latitude']);
        $this->assertNull($locationData['longitude']);
        $this->assertFalse($locationData['hasLocation']);
    }

    /** @test */
    public function it_uses_custom_getters_for_location_data()
    {
        $record = new class extends Model {
            public $custom_lat = 26.9124;
            public $custom_lng = 75.7873;
        };

        $column = LocationColumn::make('location')
            ->getLatitudeUsing(fn($record) => $record->custom_lat)
            ->getLongitudeUsing(fn($record) => $record->custom_lng);

        $locationData = $column->getLocationData($record);

        $this->assertEquals(26.9124, $locationData['latitude']);
        $this->assertEquals(75.7873, $locationData['longitude']);
        $this->assertTrue($locationData['hasLocation']);
    }

    /** @test */
    public function it_returns_default_map_controls()
    {
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

        $this->assertEquals($expectedControls, $controls);
    }

    /** @test */
    public function it_can_override_map_controls()
    {
        $customControls = [
            'zoom_control' => false,
            'map_type_control' => true,
        ];

        $column = LocationColumn::make('location')->mapControls($customControls);

        $this->assertEquals($customControls, $column->getMapControls());
    }

    /** @test */
    public function it_detects_if_record_has_location()
    {
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

        $this->assertTrue($column->hasLocation($recordWithLocation));
        $this->assertFalse($column->hasLocation($recordWithoutLocation));
    }

    /** @test */
    public function it_handles_different_icon_types()
    {
        $checkInColumn = LocationColumn::make('check_in')->iconType('check-in');
        $checkOutColumn = LocationColumn::make('check_out')->iconType('check-out');
        $homeColumn = LocationColumn::make('home')->iconType('home');
        $officeColumn = LocationColumn::make('office')->iconType('office');
        $travelColumn = LocationColumn::make('travel')->iconType('travel');

        $this->assertTrue($checkInColumn->getUseCustomIcon());
        $this->assertTrue($checkOutColumn->getUseCustomIcon());
        $this->assertTrue($homeColumn->getUseCustomIcon());
        $this->assertTrue($officeColumn->getUseCustomIcon());
        $this->assertTrue($travelColumn->getUseCustomIcon());

        // Each should have different icon content
        $this->assertNotEquals($checkInColumn->getCustomIcon(), $checkOutColumn->getCustomIcon());
        $this->assertNotEquals($homeColumn->getCustomIcon(), $officeColumn->getCustomIcon());
    }

    /** @test */
    public function it_can_be_searchable()
    {
        $column = LocationColumn::make('location')->searchable();

        $this->assertTrue($column->isSearchable());
    }

    /** @test */
    public function it_can_be_sortable()
    {
        $column = LocationColumn::make('location')->sortable();

        $this->assertTrue($column->isSortable());
    }
}
