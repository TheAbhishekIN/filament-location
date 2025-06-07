<?php

namespace App\Models;

use TheAbhishekIN\FilamentLocation\Concerns\HasLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model with JSON Location Support
 * 
 * This example demonstrates:
 * - JSON location storage (recommended)
 * - HasLocation trait usage for distance calculations
 * - Model accessors and scopes
 * - Multiple location fields (home, office, current)
 * 
 * Database Migration Example:
 * 
 * Schema::table('users', function (Blueprint $table) {
 *     $table->json('location')->nullable();
 *     $table->json('home_location')->nullable();
 *     $table->json('office_location')->nullable();
 *     $table->timestamp('location_updated_at')->nullable();
 * });
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasLocation;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'location',              // JSON location field
        'home_location',         // JSON home location
        'office_location',       // JSON office location
        'location_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'location' => 'array',              // Cast JSON to array
            'home_location' => 'array',         // Cast JSON to array
            'office_location' => 'array',       // Cast JSON to array
            'location_updated_at' => 'datetime',
        ];
    }

    /**
     * Accessors using HasLocation trait methods
     */

    /**
     * Get Google Maps URL for current location
     */
    public function getCurrentLocationMapUrlAttribute(): ?string
    {
        if (!isset($this->location['latitude']) || !isset($this->location['longitude'])) {
            return null;
        }

        return $this->generateGoogleMapsUrl(
            $this->location['latitude'],
            $this->location['longitude']
        );
    }

    /**
     * Get Google Maps URL for home location
     */
    public function getHomeLocationMapUrlAttribute(): ?string
    {
        if (!isset($this->home_location['latitude']) || !isset($this->home_location['longitude'])) {
            return null;
        }

        return $this->generateGoogleMapsUrl(
            $this->home_location['latitude'],
            $this->home_location['longitude']
        );
    }

    /**
     * Get Google Maps URL for office location
     */
    public function getOfficeLocationMapUrlAttribute(): ?string
    {
        if (!isset($this->office_location['latitude']) || !isset($this->office_location['longitude'])) {
            return null;
        }

        return $this->generateGoogleMapsUrl(
            $this->office_location['latitude'],
            $this->office_location['longitude']
        );
    }

    /**
     * Get formatted coordinates for current location
     */
    public function getCurrentCoordinatesAttribute(): ?string
    {
        if (!isset($this->location['latitude']) || !isset($this->location['longitude'])) {
            return null;
        }

        return $this->formatCoordinates(
            $this->location['latitude'],
            $this->location['longitude']
        );
    }

    /**
     * Calculate distance from current location to home
     */
    public function getDistanceToHomeAttribute(): ?float
    {
        if (!$this->hasCurrentLocation() || !$this->hasHomeLocation()) {
            return null;
        }

        return $this->calculateDistance(
            $this->location['latitude'],
            $this->location['longitude'],
            $this->home_location['latitude'],
            $this->home_location['longitude']
        );
    }

    /**
     * Calculate distance from current location to office
     */
    public function getDistanceToOfficeAttribute(): ?float
    {
        if (!$this->hasCurrentLocation() || !$this->hasOfficeLocation()) {
            return null;
        }

        return $this->calculateDistance(
            $this->location['latitude'],
            $this->location['longitude'],
            $this->office_location['latitude'],
            $this->office_location['longitude']
        );
    }

    /**
     * Calculate distance from home to office
     */
    public function getHomeToOfficeDistanceAttribute(): ?float
    {
        if (!$this->hasHomeLocation() || !$this->hasOfficeLocation()) {
            return null;
        }

        return $this->calculateDistance(
            $this->home_location['latitude'],
            $this->home_location['longitude'],
            $this->office_location['latitude'],
            $this->office_location['longitude']
        );
    }

    /**
     * Helper Methods
     */

    /**
     * Check if user has a current location
     */
    public function hasCurrentLocation(): bool
    {
        return isset($this->location['latitude']) &&
            isset($this->location['longitude']) &&
            $this->isValidCoordinate(
                $this->location['latitude'],
                $this->location['longitude']
            );
    }

    /**
     * Check if user has a home location
     */
    public function hasHomeLocation(): bool
    {
        return isset($this->home_location['latitude']) &&
            isset($this->home_location['longitude']) &&
            $this->isValidCoordinate(
                $this->home_location['latitude'],
                $this->home_location['longitude']
            );
    }

    /**
     * Check if user has an office location
     */
    public function hasOfficeLocation(): bool
    {
        return isset($this->office_location['latitude']) &&
            isset($this->office_location['longitude']) &&
            $this->isValidCoordinate(
                $this->office_location['latitude'],
                $this->office_location['longitude']
            );
    }

    /**
     * Update current location and timestamp
     */
    public function updateCurrentLocation(float $latitude, float $longitude): bool
    {
        if (!$this->isValidCoordinate($latitude, $longitude)) {
            return false;
        }

        return $this->update([
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'location_updated_at' => now(),
        ]);
    }

    /**
     * Update home location
     */
    public function updateHomeLocation(float $latitude, float $longitude): bool
    {
        if (!$this->isValidCoordinate($latitude, $longitude)) {
            return false;
        }

        return $this->update([
            'home_location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
        ]);
    }

    /**
     * Update office location
     */
    public function updateOfficeLocation(float $latitude, float $longitude): bool
    {
        if (!$this->isValidCoordinate($latitude, $longitude)) {
            return false;
        }

        return $this->update([
            'office_location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
        ]);
    }

    /**
     * Scopes
     */

    /**
     * Scope for users with current location
     */
    public function scopeWithLocation($query)
    {
        return $query->whereNotNull('location');
    }

    /**
     * Scope for users with home location
     */
    public function scopeWithHomeLocation($query)
    {
        return $query->whereNotNull('home_location');
    }

    /**
     * Scope for users with office location
     */
    public function scopeWithOfficeLocation($query)
    {
        return $query->whereNotNull('office_location');
    }

    /**
     * Get nearby users within a certain distance (in kilometers)
     */
    public function getNearbyUsers(float $distance = 10)
    {
        if (!$this->hasCurrentLocation()) {
            return collect();
        }

        return static::withLocation()
            ->where('id', '!=', $this->id)
            ->get()
            ->filter(function ($user) use ($distance) {
                if (!$user->hasCurrentLocation()) {
                    return false;
                }

                return $this->calculateDistance(
                    $this->location['latitude'],
                    $this->location['longitude'],
                    $user->location['latitude'],
                    $user->location['longitude']
                ) <= $distance;
            });
    }

    /**
     * Get the closest user to this user
     */
    public function getClosestUser()
    {
        return $this->getNearbyUsers(1000)->sortBy(function ($user) {
            return $this->calculateDistance(
                $this->location['latitude'],
                $this->location['longitude'],
                $user->location['latitude'],
                $user->location['longitude']
            );
        })->first();
    }

    /**
     * Check if user is at home (within 100 meters)
     */
    public function isAtHome(float $radiusKm = 0.1): bool
    {
        if (!$this->hasCurrentLocation() || !$this->hasHomeLocation()) {
            return false;
        }

        return $this->distance_to_home <= $radiusKm;
    }

    /**
     * Check if user is at office (within 100 meters)
     */
    public function isAtOffice(float $radiusKm = 0.1): bool
    {
        if (!$this->hasCurrentLocation() || !$this->hasOfficeLocation()) {
            return false;
        }

        return $this->distance_to_office <= $radiusKm;
    }

    /**
     * Export location data for reporting
     */
    public function toLocationArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'current_location' => $this->location,
            'home_location' => $this->home_location,
            'office_location' => $this->office_location,
            'current_coordinates' => $this->current_coordinates,
            'distance_to_home' => $this->distance_to_home,
            'distance_to_office' => $this->distance_to_office,
            'location_updated_at' => $this->location_updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
