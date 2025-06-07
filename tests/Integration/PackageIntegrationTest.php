<?php

namespace TheAbhishekIN\FilamentLocation\Tests\Integration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TheAbhishekIN\FilamentLocation\Concerns\HasLocation;
use TheAbhishekIN\FilamentLocation\Tables\Columns\LocationColumn;
use TheAbhishekIN\FilamentLocation\Tests\TestCase;

class PackageIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test table
        Schema::create('test_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('location')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /** @test */
    public function it_handles_legacy_separate_lat_lng_fields()
    {
        $record = TestLocationModel::create([
            'name' => 'Legacy Location',
            'latitude' => 26.9124,
            'longitude' => 75.7873,
        ]);

        $column = LocationColumn::make('location')
            ->latitude('latitude')
            ->longitude('longitude');

        $locationData = $column->getLocationData($record);

        $this->assertEquals(26.9124, $locationData['latitude']);
        $this->assertEquals(75.7873, $locationData['longitude']);
        $this->assertTrue($locationData['hasLocation']);
    }

    /** @test */
    public function location_column_works_with_different_icon_types()
    {
        $record = TestLocationModel::create([
            'name' => 'Check-in Location',
            'latitude' => 26.9124,
            'longitude' => 75.7873,
        ]);

        $checkInColumn = LocationColumn::make('location')
            ->iconType('check-in')
            ->title('Check-In Location')
            ->latitude('latitude')
            ->longitude('longitude');

        $this->assertTrue($checkInColumn->getUseCustomIcon());
        $this->assertEquals('Check-In Location', $checkInColumn->getTitle());
        $this->assertNotNull($checkInColumn->getCustomIcon());
    }

    /** @test */
    public function package_configuration_is_accessible()
    {
        $apiKey = config('filament-location.google_maps_api_key');
        $zoom = config('filament-location.default_zoom');
        $height = config('filament-location.map_height');

        $this->assertEquals('test-api-key', $apiKey);
        $this->assertEquals(15, $zoom);
        $this->assertEquals('400px', $height);
    }

    /** @test */
    public function it_handles_empty_location_gracefully()
    {
        $record = TestLocationModel::create([
            'name' => 'No Location',
            'latitude' => null,
            'longitude' => null,
        ]);

        $column = LocationColumn::make('location')
            ->latitude('latitude')
            ->longitude('longitude');

        $this->assertFalse($column->hasLocation($record));

        $locationData = $column->getLocationData($record);
        $this->assertNull($locationData['latitude']);
        $this->assertNull($locationData['longitude']);
        $this->assertFalse($locationData['hasLocation']);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_locations');
        parent::tearDown();
    }
}

class TestLocationModel extends Model
{
    use HasLocation;

    protected $table = 'test_locations';

    protected $fillable = ['name', 'location', 'latitude', 'longitude'];

    protected $casts = [
        'location' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
}
