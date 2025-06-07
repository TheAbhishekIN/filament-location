<?php

namespace TheAbhishekIN\FilamentLocation\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasLocation
{
    /**
     * Initialize the HasLocation trait.
     */
    public function initializeHasLocation(): void
    {
        $this->fillable = array_merge($this->fillable ?? [], [
            'latitude',
            'longitude',
        ]);

        $this->casts = array_merge($this->casts ?? [], [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ]);
    }

    /**
     * Get the location coordinates as an array.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function getLocationAttribute(): ?array
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
        ];
    }

    /**
     * Check if the model has a location.
     */
    public function hasLocation(): bool
    {
        return $this->latitude && $this->longitude;
    }

    /**
     * Get the Google Maps URL for this location.
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if (!$this->hasLocation()) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Get the coordinates formatted as a string.
     */
    public function getCoordinatesStringAttribute(): ?string
    {
        if (!$this->hasLocation()) {
            return null;
        }

        return "{$this->latitude}, {$this->longitude}";
    }

    /**
     * Calculate distance to another location in kilometers.
     */
    public function distanceTo(float $latitude, float $longitude): ?float
    {
        if (!$this->hasLocation()) {
            return null;
        }

        return $this->calculateDistance(
            (float) $this->latitude,
            (float) $this->longitude,
            $latitude,
            $longitude
        );
    }

    /**
     * Calculate distance to another model with location.
     *
     * @param object $model
     */
    public function distanceToModel($model): ?float
    {
        if (!method_exists($model, 'hasLocation') || !$model->hasLocation() || !$this->hasLocation()) {
            return null;
        }

        return $this->distanceTo((float) $model->latitude, (float) $model->longitude);
    }

    /**
     * Scope to find models within a certain distance.
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeWithinDistance(Builder $query, float $latitude, float $longitude, float $distance): Builder
    {
        return $query->whereRaw(
            "ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?)) <= ?",
            [$longitude, $latitude, $distance * 1000] // Convert km to meters
        );
    }

    /**
     * Scope to order by distance from a point.
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeOrderByDistance(Builder $query, float $latitude, float $longitude): Builder
    {
        return $query->orderByRaw(
            "ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?))",
            [$longitude, $latitude]
        );
    }

    /**
     * Calculate the distance between two points using the Haversine formula.
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Update the location and set the updated timestamp.
     */
    public function updateLocation(float $latitude, float $longitude): bool
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        if ($this->hasColumn('location_updated_at')) {
            $this->location_updated_at = now();
        }

        return $this->save();
    }

    /**
     * Clear the location data.
     */
    public function clearLocation(): bool
    {
        $this->latitude = null;
        $this->longitude = null;

        if ($this->hasColumn('location_updated_at')) {
            $this->location_updated_at = null;
        }

        return $this->save();
    }

    /**
     * Check if the model has a specific column.
     */
    protected function hasColumn(string $column): bool
    {
        return in_array($column, $this->getFillable()) ||
            array_key_exists($column, $this->getCasts());
    }

    /**
     * Generate Google Maps URL for the given coordinates.
     */
    protected function generateGoogleMapsUrl(float $latitude, float $longitude, ?int $zoom = null): ?string
    {
        if (!$this->isValidCoordinate($latitude, $longitude)) {
            return null;
        }

        $url = "https://www.google.com/maps?q={$latitude},{$longitude}";

        if ($zoom) {
            $url .= "&z={$zoom}";
        }

        return $url;
    }

    /**
     * Validate latitude value.
     */
    protected function isValidLatitude(?float $latitude): bool
    {
        return $latitude !== null && $latitude >= -90 && $latitude <= 90;
    }

    /**
     * Validate longitude value.
     */
    protected function isValidLongitude(?float $longitude): bool
    {
        return $longitude !== null && $longitude >= -180 && $longitude <= 180;
    }

    /**
     * Validate coordinate pair.
     */
    protected function isValidCoordinate(?float $latitude, ?float $longitude): bool
    {
        return $this->isValidLatitude($latitude) && $this->isValidLongitude($longitude);
    }

    /**
     * Format coordinates for display.
     */
    protected function formatCoordinates(float $latitude, float $longitude, int $precision = 6): string
    {
        return sprintf(
            '%.6f, %.6f',
            round($latitude, $precision),
            round($longitude, $precision)
        );
    }

    /**
     * Convert degrees to radians.
     */
    protected function degreesToRadians(float $degrees): float
    {
        return deg2rad($degrees);
    }
}
