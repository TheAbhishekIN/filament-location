# Filament Location Package - Usage Guide

## Quick Start Guide

### Step 1: Install the Package

```bash
composer require theabhishekin/filament-location
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --tag="filament-location-config"
```

### Step 3: Add Google Maps API Key

Add your Google Maps API key to your `.env` file:

```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

**Getting a Google Maps API Key:**

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the "Maps JavaScript API" and "Geocoding API"
4. Create credentials (API Key)
5. Restrict the API key to your domain for security

### Step 4: Prepare Your Database

Add location columns to your existing table:

```php
// In a migration file
Schema::table('users', function (Blueprint $table) {
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->timestamp('location_updated_at')->nullable();
});
```

Or publish and run the example migration:

```bash
php artisan vendor:publish --tag="filament-location-migrations"
php artisan migrate
```

### Step 5: Update Your Model

Add the `HasLocation` trait to your model:

```php
<?php

namespace App\Models;

use TheAbhishekIN\FilamentLocation\Concerns\HasLocation;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasLocation;

    protected $fillable = [
        'name',
        'email',
        // The trait will automatically add 'latitude' and 'longitude'
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'location_updated_at' => 'datetime',
            // The trait will automatically add casts for location fields
        ];
    }
}
```

### Step 6: Update Your Filament Resource

#### Form (for collecting location):

```php
use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Your existing fields...

            LocationPicker::make('location')
                ->label('Current Location')
                ->latitude('latitude')
                ->longitude('longitude')
                ->zoom(15)
                ->height('300px'),
        ]);
}
```

#### Table (for displaying location):

```php
use TheAbhishekIN\FilamentLocation\Tables\Columns\LocationColumn;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            // Your existing columns...

            LocationColumn::make('location')
                ->label('Location')
                ->latitude('latitude')
                ->longitude('longitude')
                ->zoom(15)
                ->height('400px'),
        ]);
}
```

## Advanced Usage

### Custom Configuration

Edit `config/filament-location.php`:

```php
return [
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    'default_zoom' => 15,
    'map_height' => '400px',
    'enable_street_view' => true,
    'map_type' => 'standard', // standard, satellite, hybrid, terrain
    'location_accuracy' => 100,
    'location_timeout' => 10000,
    'enable_high_accuracy' => true,
    'map_controls' => [
        'zoom_control' => true,
        'map_type_control' => true,
        'scale_control' => true,
        'street_view_control' => true,
        'rotate_control' => true,
        'fullscreen_control' => true,
    ],
];
```

### LocationPicker Options

```php
LocationPicker::make('location')
    ->latitude('latitude')           // Required: database column for latitude
    ->longitude('longitude')         // Required: database column for longitude
    ->zoom(15)                      // Optional: map zoom level (1-20)
    ->height('300px')               // Optional: picker height
    ->showMap(true)                 // Optional: show/hide map
    ->showCoordinates(true)         // Optional: show/hide coordinates display
    ->mapType('standard')           // Optional: standard, satellite, hybrid, terrain
    ->mapControls([                 // Optional: customize map controls
        'zoom_control' => true,
        'map_type_control' => false,
    ])
    ->helperText('Click "Get Current Location" to capture your location')
```

### LocationColumn Options

```php
LocationColumn::make('location')
    ->latitude('latitude')           // Required: database column for latitude
    ->longitude('longitude')         // Required: database column for longitude
    ->zoom(15)                      // Optional: modal map zoom level
    ->height('400px')               // Optional: modal map height
    ->iconSize('lg')                // Optional: sm, md, lg, xl
    ->iconColor('primary')          // Optional: primary, secondary, success, warning, danger
    ->showTooltip(true)             // Optional: show tooltip on hover
    ->tooltipText('View location')  // Optional: custom tooltip text
    ->mapType('satellite')          // Optional: map type for modal
```

### Using the HasLocation Trait

The trait provides several helpful methods:

```php
$user = User::find(1);

// Check if user has location
if ($user->hasLocation()) {
    echo "User has location!";
}

// Get location as array
$location = $user->location; // ['latitude' => 40.7128, 'longitude' => -74.0060]

// Get Google Maps URL
$url = $user->google_maps_url; // https://www.google.com/maps?q=40.7128,-74.0060

// Get coordinates as string
$coords = $user->coordinates_string; // "40.7128, -74.0060"

// Calculate distance to another location (in kilometers)
$distance = $user->distanceTo(40.7589, -73.9851); // Distance to Times Square

// Calculate distance to another user
$otherUser = User::find(2);
$distance = $user->distanceToModel($otherUser);

// Update location programmatically
$user->updateLocation(40.7128, -74.0060);

// Clear location
$user->clearLocation();
```

### Location-based Queries

```php
// Find users within 10km of a point
$nearbyUsers = User::withinDistance(40.7128, -74.0060, 10)->get();

// Order users by distance from a point
$users = User::orderByDistance(40.7128, -74.0060)->get();

// Get users with locations only
$usersWithLocation = User::withLocation()->get();

// Combine queries
$nearbyUsers = User::withLocation()
    ->withinDistance(40.7128, -74.0060, 5)
    ->orderByDistance(40.7128, -74.0060)
    ->take(10)
    ->get();
```

### Custom User Methods

Add these methods to your User model for additional functionality:

```php
public function getNearbyUsers(float $distance = 10)
{
    if (!$this->hasLocation()) {
        return collect();
    }

    return static::withinDistance($this->latitude, $this->longitude, $distance)
        ->where('id', '!=', $this->id)
        ->orderByDistance($this->latitude, $this->longitude)
        ->get();
}

public function getClosestUser()
{
    return $this->getNearbyUsers(1000)->first();
}
```

## Troubleshooting

### Common Issues

1. **Map not loading**: Check your Google Maps API key and ensure the Maps JavaScript API is enabled.

2. **Location permission denied**: The browser will ask for permission. Users must allow location access.

3. **Inaccurate location**: Enable high accuracy in configuration for GPS-based location.

4. **Database errors**: Ensure latitude and longitude columns exist and are of type `decimal(10,8)` and `decimal(11,8)` respectively.

### Browser Compatibility

-   **HTTPS Required**: Geolocation API requires HTTPS in production
-   **Supported Browsers**: Chrome, Firefox, Safari, Edge (modern versions)
-   **Mobile Support**: Full support on iOS Safari and Android Chrome

### Performance Considerations

-   Google Maps API calls are cached automatically
-   Location queries use database indexes for performance
-   Consider rate limiting location updates to avoid API quota issues

## Security Best Practices

1. **Restrict API Key**: Limit your Google Maps API key to specific domains
2. **Validate Coordinates**: Always validate latitude/longitude ranges server-side
3. **Rate Limiting**: Implement rate limiting for location updates
4. **Privacy**: Inform users about location data collection and storage

## Contributing

If you find any issues or have suggestions for improvements, please create an issue or submit a pull request on GitHub.
