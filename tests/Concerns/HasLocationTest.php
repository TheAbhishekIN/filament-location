<?php

namespace TheAbhishekIN\FilamentLocation\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use TheAbhishekIN\FilamentLocation\Concerns\HasLocation;
use TheAbhishekIN\FilamentLocation\Tests\TestCase;

class HasLocationTest extends TestCase
{
    /** @test */
    public function it_calculates_distance_between_coordinates()
    {
        $model = new TestModel();

        // Distance between Jaipur (26.9124, 75.7873) and Delhi (28.7041, 77.1025)
        $distance = $model->calculateDistance(26.9124, 75.7873, 28.7041, 77.1025);

        // Approximate distance between Jaipur and Delhi is ~280km
        $this->assertGreaterThan(270, $distance);
        $this->assertLessThan(290, $distance);
    }

    /** @test */
    public function it_calculates_zero_distance_for_same_coordinates()
    {
        $model = new TestModel();

        $distance = $model->calculateDistance(26.9124, 75.7873, 26.9124, 75.7873);

        $this->assertEquals(0, $distance);
    }

    /** @test */
    public function it_generates_google_maps_url()
    {
        $model = new TestModel();

        $url = $model->generateGoogleMapsUrl(26.9124, 75.7873);

        $expectedUrl = 'https://www.google.com/maps?q=26.9124,75.7873';
        $this->assertEquals($expectedUrl, $url);
    }

    /** @test */
    public function it_generates_google_maps_url_with_custom_zoom()
    {
        $model = new TestModel();

        $url = $model->generateGoogleMapsUrl(26.9124, 75.7873, 18);

        $expectedUrl = 'https://www.google.com/maps?q=26.9124,75.7873&z=18';
        $this->assertEquals($expectedUrl, $url);
    }

    /** @test */
    public function it_validates_latitude_range()
    {
        $model = new TestModel();

        $this->assertTrue($model->isValidLatitude(0));
        $this->assertTrue($model->isValidLatitude(90));
        $this->assertTrue($model->isValidLatitude(-90));
        $this->assertTrue($model->isValidLatitude(26.9124));

        $this->assertFalse($model->isValidLatitude(91));
        $this->assertFalse($model->isValidLatitude(-91));
        $this->assertFalse($model->isValidLatitude(180));
    }

    /** @test */
    public function it_validates_longitude_range()
    {
        $model = new TestModel();

        $this->assertTrue($model->isValidLongitude(0));
        $this->assertTrue($model->isValidLongitude(180));
        $this->assertTrue($model->isValidLongitude(-180));
        $this->assertTrue($model->isValidLongitude(75.7873));

        $this->assertFalse($model->isValidLongitude(181));
        $this->assertFalse($model->isValidLongitude(-181));
        $this->assertFalse($model->isValidLongitude(200));
    }

    /** @test */
    public function it_validates_coordinate_pairs()
    {
        $model = new TestModel();

        $this->assertTrue($model->isValidCoordinate(26.9124, 75.7873));
        $this->assertTrue($model->isValidCoordinate(0, 0));
        $this->assertTrue($model->isValidCoordinate(90, 180));
        $this->assertTrue($model->isValidCoordinate(-90, -180));

        $this->assertFalse($model->isValidCoordinate(91, 75.7873));
        $this->assertFalse($model->isValidCoordinate(26.9124, 181));
        $this->assertFalse($model->isValidCoordinate(91, 181));
    }

    /** @test */
    public function it_formats_coordinates_for_display()
    {
        $model = new TestModel();

        $formatted = $model->formatCoordinates(26.9124, 75.7873);
        $this->assertEquals('26.912400, 75.787300', $formatted);

        $formatted = $model->formatCoordinates(26.9124, 75.7873, 2);
        $this->assertEquals('26.91, 75.79', $formatted);
    }

    /** @test */
    public function it_converts_degrees_to_radians()
    {
        $model = new TestModel();

        $radians = $model->degreesToRadians(180);
        $this->assertEqualsWithDelta(pi(), $radians, 0.0001);

        $radians = $model->degreesToRadians(90);
        $this->assertEqualsWithDelta(pi() / 2, $radians, 0.0001);
    }

    /** @test */
    public function it_handles_null_coordinates_gracefully()
    {
        $model = new TestModel();

        $distance = $model->calculateDistance(null, null, 26.9124, 75.7873);
        $this->assertNull($distance);

        $distance = $model->calculateDistance(26.9124, 75.7873, null, null);
        $this->assertNull($distance);

        $url = $model->generateGoogleMapsUrl(null, null);
        $this->assertNull($url);

        $this->assertFalse($model->isValidCoordinate(null, 75.7873));
        $this->assertFalse($model->isValidCoordinate(26.9124, null));
    }

    /** @test */
    public function it_calculates_bearing_between_coordinates()
    {
        $model = new TestModel();

        // Test bearing calculation (if implemented)
        if (method_exists($model, 'calculateBearing')) {
            $bearing = $model->calculateBearing(26.9124, 75.7873, 28.7041, 77.1025);
            $this->assertIsFloat($bearing);
            $this->assertGreaterThanOrEqual(0, $bearing);
            $this->assertLessThan(360, $bearing);
        }
    }

    /** @test */
    public function it_finds_midpoint_between_coordinates()
    {
        $model = new TestModel();

        // Test midpoint calculation (if implemented)
        if (method_exists($model, 'calculateMidpoint')) {
            $midpoint = $model->calculateMidpoint(26.9124, 75.7873, 28.7041, 77.1025);
            $this->assertIsArray($midpoint);
            $this->assertArrayHasKey('latitude', $midpoint);
            $this->assertArrayHasKey('longitude', $midpoint);
        }
    }
}

class TestModel extends Model
{
    use HasLocation;

    protected $fillable = ['latitude', 'longitude'];

    // Make trait methods public for testing
    public function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        return parent::calculateDistance($lat1, $lng1, $lat2, $lng2);
    }

    public function generateGoogleMapsUrl($latitude, $longitude, $zoom = null)
    {
        return parent::generateGoogleMapsUrl($latitude, $longitude, $zoom);
    }

    public function isValidLatitude($latitude)
    {
        return parent::isValidLatitude($latitude);
    }

    public function isValidLongitude($longitude)
    {
        return parent::isValidLongitude($longitude);
    }

    public function isValidCoordinate($latitude, $longitude)
    {
        return parent::isValidCoordinate($latitude, $longitude);
    }

    public function formatCoordinates($latitude, $longitude, $precision = 6)
    {
        return parent::formatCoordinates($latitude, $longitude, $precision);
    }

    public function degreesToRadians($degrees)
    {
        return parent::degreesToRadians($degrees);
    }
}
