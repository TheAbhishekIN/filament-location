<?php

namespace TheAbhishekIN\FilamentLocation\Tests\Integration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TheAbhishekIN\FilamentLocation\Concerns\HasLocation;
use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;
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
    public function it_can_save_location_data_through_form()
    {
        $locationData = ['latitude' => 26.9124, 'longitude' => 75.7873];

        $record = TestLocationModel::create([
            'name' => 'Test Location',
            'location' => $locationData,
        ]);

        $this->assertDatabaseHas('test_locations', [
            'name' => 'Test Location',
            'location' => json_encode($locationData),
        ]);

        $this->assertEquals($locationData['latitude'], $record->location['latitude']);
        $this->assertEquals($locationData['longitude'], $record->location['longitude']);
    }

    /** @test */
    public function it_can_display_location_data_in_table()
    {
        $record = TestLocationModel::create([
            'name' => 'Test Location',
            'location' => ['latitude' => 26.9124, 'longitude' => 75.7873],
        ]);

        $column = LocationColumn::make('location')
            ->getLatitudeUsing(fn($record) => $record->location['latitude'] ?? null)
            ->getLongitudeUsing(fn($record) => $record->location['longitude'] ?? null);

        $locationData = $column->getLocationData($record);

        $this->assertEquals(26.9124, $locationData['latitude']);
        $this->assertEquals(75.7873, $locationData['longitude']);
        $this->assertTrue($locationData['hasLocation']);
    }

    /** @test */
    public function it_can_calculate_distance_between_locations()
    {
        $location1 = TestLocationModel::create([
            'name' => 'Location 1',
            'location' => ['latitude' => 26.9124, 'longitude' => 75.7873], // Jaipur
        ]);

        $location2 = TestLocationModel::create([
            'name' => 'Location 2',
            'location' => ['latitude' => 28.7041, 'longitude' => 77.1025], // Delhi
        ]);

        $distance = $location1->calculateDistanceToLocation($location2);

        // Distance between Jaipur and Delhi is approximately 280km
        $this->assertGreaterThan(270, $distance);
        $this->assertLessThan(290, $distance);
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
    public function it_generates_google_maps_urls()
    {
        $record = TestLocationModel::create([
            'name' => 'Test Location',
            'location' => ['latitude' => 26.9124, 'longitude' => 75.7873],
        ]);

        $url = $record->google_maps_url;
        $expectedUrl = 'https://www.google.com/maps?q=26.9124,75.7873';

        $this->assertEquals($expectedUrl, $url);
    }

    /** @test */
    public function location_picker_validates_required_data()
    {
        $picker = LocationPicker::make('location')->required();

        // Test with null data
        $picker->state(null);
        $this->assertTrue($picker->hasValidationErrors(['required']));

        // Test with empty array
        $picker->state([]);
        $this->assertTrue($picker->hasValidationErrors(['required']));

        // Test with valid data
        $picker->state(['latitude' => 26.9124, 'longitude' => 75.7873]);
        $this->assertFalse($picker->hasValidationErrors(['required']));
    }

    /** @test */
    public function location_picker_handles_json_hydration()
    {
        $picker = LocationPicker::make('location');

        $jsonString = '{"latitude":26.9124,"longitude":75.7873}';
        $picker->state($jsonString);

        $state = $picker->getState();
        $this->assertIsArray($state);
        $this->assertEquals(26.9124, $state['latitude']);
        $this->assertEquals(75.7873, $state['longitude']);
    }

    /** @test */
    public function location_column_works_with_different_icon_types()
    {
        $record = TestLocationModel::create([
            'name' => 'Check-in Location',
            'location' => ['latitude' => 26.9124, 'longitude' => 75.7873],
        ]);

        $checkInColumn = LocationColumn::make('location')
            ->iconType('check-in')
            ->title('Check-In Location')
            ->getLatitudeUsing(fn($record) => $record->location['latitude'] ?? null)
            ->getLongitudeUsing(fn($record) => $record->location['longitude'] ?? null);

        $this->assertTrue($checkInColumn->getUseCustomIcon());
        $this->assertEquals('Check-In Location', $checkInColumn->getTitle());
        $this->assertNotNull($checkInColumn->getCustomIcon());
    }

    /** @test */
    public function package_configuration_is_accessible()
    {
        $apiKey = config('filament-location.google_maps_api_key');
        $zoom = config('filament-location.default_zoom');
        $height = config('filament-location.default_height');

        $this->assertEquals('test-api-key', $apiKey);
        $this->assertEquals(15, $zoom);
        $this->assertEquals('400px', $height);
    }

    /** @test */
    public function it_handles_empty_location_gracefully()
    {
        $record = TestLocationModel::create([
            'name' => 'No Location',
            'location' => null,
        ]);

        $column = LocationColumn::make('location')
            ->getLatitudeUsing(fn($record) => $record->location['latitude'] ?? null)
            ->getLongitudeUsing(fn($record) => $record->location['longitude'] ?? null);

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

    public function getGoogleMapsUrlAttribute(): ?string
    {
        if (isset($this->location['latitude']) && isset($this->location['longitude'])) {
            return $this->generateGoogleMapsUrl(
                $this->location['latitude'],
                $this->location['longitude']
            );
        }

        if ($this->latitude && $this->longitude) {
            return $this->generateGoogleMapsUrl($this->latitude, $this->longitude);
        }

        return null;
    }

    public function calculateDistanceToLocation(TestLocationModel $other): ?float
    {
        $lat1 = $this->location['latitude'] ?? $this->latitude;
        $lng1 = $this->location['longitude'] ?? $this->longitude;
        $lat2 = $other->location['latitude'] ?? $other->latitude;
        $lng2 = $other->location['longitude'] ?? $other->longitude;

        if (!$lat1 || !$lng1 || !$lat2 || !$lng2) {
            return null;
        }

        return $this->calculateDistance($lat1, $lng1, $lat2, $lng2);
    }
}
