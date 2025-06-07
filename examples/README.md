# Filament Location Package - Examples

This directory contains practical examples demonstrating how to use the Filament Location package with JSON location storage. The examples focus on a User model with multiple location types.

## 📁 Example Files

### [`User.php`](User.php)

**User model with JSON location support**

Features demonstrated:

-   JSON location storage with proper casting
-   HasLocation trait usage for distance calculations
-   Multiple location types (current, home, office)
-   Model accessors for common location operations

```php
// Example usage
$user = User::find(1);
echo $user->distance_to_home; // Distance from current to home
echo $user->isAtOffice(); // Check if user is at office
$nearbyUsers = $user->getNearbyUsers(5); // Users within 5km
```

### [`UserResource.php`](UserResource.php)

**Filament resource for user management with locations**

Features demonstrated:

-   LocationPicker with advanced configuration
-   LocationColumn with dynamic icons
-   Multiple location fields in forms and tables
-   Filtering by location presence

## 🚀 Quick Start Guide

### 1. Set up your database

Add location fields to your users table:

```php
Schema::table('users', function (Blueprint $table) {
    $table->json('location')->nullable();
    $table->json('home_location')->nullable();
    $table->json('office_location')->nullable();
    $table->timestamp('location_updated_at')->nullable();
});
```

### 2. Update your User model

```php
use TheAbhishekIN\FilamentLocation\Concerns\HasLocation;

class User extends Authenticatable
{
    use HasLocation;

    protected $fillable = [
        'name', 'email', 'password',
        'location', 'home_location', 'office_location', 'location_updated_at',
    ];

    protected $casts = [
        'location' => 'array',
        'home_location' => 'array',
        'office_location' => 'array',
        'location_updated_at' => 'datetime',
    ];
}
```

### 3. Create Filament resource

```bash
php artisan make:filament-resource User
```

Then use the provided UserResource.php as a reference for implementing location features.

### 4. Configure Google Maps API

Add your API key to `.env`:

```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

## 💡 Usage Examples

### Basic LocationPicker

```php
LocationPicker::make('location')
    ->label('Current Location')
    ->required()
    ->zoom(16)
    ->height('400px');
```

### Basic LocationColumn

```php
LocationColumn::make('location')
    ->label('Current Location')
    ->iconType('home')
    ->getLatitudeUsing(fn($record) => $record->location['latitude'] ?? null)
    ->getLongitudeUsing(fn($record) => $record->location['longitude'] ?? null);
```

### Multiple Location Types

```php
// Form - Multiple location pickers
LocationPicker::make('home_location')
    ->label('Home Location'),

LocationPicker::make('office_location')
    ->label('Office Location'),

LocationPicker::make('location')
    ->label('Current Location'),

// Table - Multiple location columns
LocationColumn::make('home_location')
    ->label('Home')
    ->iconType('home')
    ->getLatitudeUsing(fn($record) => $record->home_location['latitude'] ?? null)
    ->getLongitudeUsing(fn($record) => $record->home_location['longitude'] ?? null),

LocationColumn::make('office_location')
    ->label('Office')
    ->iconType('office')
    ->getLatitudeUsing(fn($record) => $record->office_location['latitude'] ?? null)
    ->getLongitudeUsing(fn($record) => $record->office_location['longitude'] ?? null),
```

## 🎨 Icon Types

Available icon types for LocationColumn:

| Icon Type   | Use Case            | Color  |
| ----------- | ------------------- | ------ |
| `home`      | Home addresses      | Blue   |
| `office`    | Office locations    | Purple |
| `check-in`  | Check-in locations  | Green  |
| `check-out` | Check-out locations | Orange |
| `travel`    | Travel locations    | Red    |

```php
LocationColumn::make('location')
    ->iconType('home')
    ->title('User Location'),
```

## 🔧 Advanced Features

### Distance Calculations

```php
// In your User model
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

// Usage
$user = User::find(1);
echo $user->distance_to_office . ' km';
```

### Location Validation

```php
LocationPicker::make('location')
    ->required()
    ->rules([
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ]);
```

### Custom Map Controls

```php
LocationPicker::make('location')
    ->zoom(16)
    ->mapType('satellite')
    ->mapControls([
        'zoom_control' => true,
        'map_type_control' => false,
        'street_view_control' => true,
    ]);
```

### Export User Locations

```php
Tables\Actions\BulkAction::make('export_locations')
    ->label('Export Locations')
    ->action(function ($records) {
        return response()->streamDownload(function () use ($records) {
            echo "Name,Email,Current Location,Home Location\n";
            $records->each(function ($record) {
                $current = $record->location ?
                    $record->location['latitude'] . ',' . $record->location['longitude'] : 'null,null';
                $home = $record->home_location ?
                    $record->home_location['latitude'] . ',' . $record->home_location['longitude'] : 'null,null';
                echo "{$record->name},{$record->email},{$current},{$home}\n";
            });
        }, 'user-locations.csv');
    });
```

## 🔍 Troubleshooting

### Location not saving

```php
// Check model setup
protected $casts = [
    'location' => 'array',
];

protected $fillable = [
    'location',
];
```

### Map not loading

-   Check Google Maps API key in `.env`
-   Verify Maps JavaScript API is enabled
-   Check browser console for errors

### Icons not displaying

```bash
php artisan view:clear
php artisan vendor:publish --tag="filament-location-views"
```

## 📝 Best Practices

-   Use JSON fields for location storage
-   Always validate location data
-   Handle location permissions gracefully
-   Cache distance calculations for performance
-   Provide clear user instructions

## 📚 Resources

-   [Main Package Documentation](../README.md)
-   [Testing Guide](../TESTING.md)
-   [Google Maps API Documentation](https://developers.google.com/maps/documentation)
-   [Filament Documentation](https://filamentphp.com/docs)
