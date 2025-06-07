# Filament Location

[![Latest Version on Packagist](https://img.shields.io/packagist/v/theabhishekin/filament-location.svg?style=flat-square)](https://packagist.org/packages/theabhishekin/filament-location)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/theabhishekin/filament-location/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/theabhishekin/filament-location/actions/workflows/tests.yml)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/theabhishekin/filament-location/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/theabhishekin/filament-location/actions/workflows/fix-php-code-style-issues.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/theabhishekin/filament-location.svg?style=flat-square)](https://packagist.org/packages/theabhishekin/filament-location)

A powerful Filament package for collecting and displaying user locations with Google Maps integration. This package provides easy-to-use location picker and display components for your Filament admin panels.

## Features

- 🗺️ **LocationPicker**: Interactive Google Maps component for forms
- 📍 **LocationColumn**: Display locations in tables with clickable map modals
- 🎯 **Current Location**: Get user's current location with GPS
- 🔧 **Highly Customizable**: Configurable zoom, map types, controls, and styling
- 📱 **Responsive Design**: Works perfectly on desktop and mobile
- 🌍 **Multiple Location Types**: Support for home, office, check-in/out locations
- 📊 **Distance Calculations**: Built-in distance calculation utilities
- 🎨 **Custom Icons**: Predefined and custom icon support

## Installation

You can install the package via Composer:

```bash
composer require theabhishekin/filament-location
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-location-config"
```

Add your Google Maps API key to your `.env` file:

```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

> **Note**: Make sure to enable the **Maps JavaScript API** and **Geocoding API** in your Google Cloud Console.

## Configuration

The configuration file allows you to customize default settings:

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

## Database Migration

Add location fields to your model's migration:

```php
Schema::table('users', function (Blueprint $table) {
    $table->json('location')->nullable();
    $table->timestamp('location_updated_at')->nullable();
});
```

## Model Setup

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
        'location',
        'location_updated_at',
    ];

    protected $casts = [
        'location' => 'array',
        'location_updated_at' => 'datetime',
    ];
}
```

## Quick Example: User Resource

Here's a complete example of how to use the location components in a Filament resource:

```php
<?php

namespace App\Filament\Resources;

use App\Models\User;
use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;
use TheAbhishekIN\FilamentLocation\Tables\Columns\LocationColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                LocationPicker::make('location')
                    ->label('User Location')
                    ->required()
                    ->zoom(16)
                    ->height('400px')
                    ->mapType('standard')
                    ->showCoordinates(true)
                    ->helperText('Click on the map or use "Get Current Location" to set the location'),

                Forms\Components\DateTimePicker::make('location_updated_at')
                    ->label('Location Updated At')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                LocationColumn::make('location')
                    ->label('Location')
                    ->iconType('home')
                    ->iconSize('lg')
                    ->iconColor('primary')
                    ->title('User Location')
                    ->showTooltip(true)
                    ->tooltipText('Click to view location on map')
                    ->getLatitudeUsing(fn($record) => $record->location['latitude'] ?? null)
                    ->getLongitudeUsing(fn($record) => $record->location['longitude'] ?? null),

                Tables\Columns\TextColumn::make('location_updated_at')
                    ->label('Location Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_location')
                    ->label('Has Location')
                    ->query(fn($query) => $query->whereNotNull('location'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
```

## Usage

### LocationPicker Component

```php
LocationPicker::make('location')
    ->label('Select Location')
    ->required()
    ->zoom(15)                    // Map zoom level (1-20)
    ->height('400px')             // Map container height
    ->mapType('standard')         // standard, satellite, hybrid, terrain
    ->showCoordinates(true)       // Show latitude/longitude coordinates
    ->showMap(true)               // Show/hide the map
    ->mapControls([               // Configure map controls
        'zoom_control' => true,
        'street_view_control' => false,
    ])
    ->helperText('Click on the map to select a location');
```

### LocationColumn Component

```php
LocationColumn::make('location')
    ->label('Location')
    ->iconType('home')                    // home, office, check-in, check-out, travel
    ->iconSize('lg')                      // sm, md, lg, xl
    ->iconColor('primary')                // primary, success, warning, danger
    ->title('Location Details')           // Modal title
    ->showTooltip(true)                   // Show hover tooltip
    ->tooltipText('View on map')          // Custom tooltip text
    ->getLatitudeUsing(fn($record) => $record->location['latitude'] ?? null)
    ->getLongitudeUsing(fn($record) => $record->location['longitude'] ?? null);
```

## Available Icon Types

| Icon Type   | Description         | Default Color |
| ----------- | ------------------- | ------------- |
| `home`      | Home locations      | Blue          |
| `office`    | Office locations    | Purple        |
| `check-in`  | Check-in locations  | Green         |
| `check-out` | Check-out locations | Orange        |
| `travel`    | Travel locations    | Red           |

## Use Cases

- **Employee Management**: Track employee locations for check-in/check-out
- **Customer Management**: Store customer addresses and locations
- **Venue Management**: Manage multiple business locations
- **Event Management**: Record event locations and venue details
- **Delivery Services**: Track delivery addresses and routes
- **Real Estate**: Manage property locations
- **Healthcare**: Patient and facility location management

## Distance Calculations

The `HasLocation` trait provides distance calculation utilities:

```php
// Calculate distance between two points
$distance = $user->distanceTo(26.9124, 75.7873); // Returns distance in kilometers

// Calculate distance to another model
$distance = $user->distanceToModel($otherUser);

// Find users within 10km radius
$nearbyUsers = User::withinDistance(26.9124, 75.7873, 10)->get();

// Order users by distance from a point
$usersByDistance = User::orderByDistance(26.9124, 75.7873)->get();
```

## Security & HTTPS Requirements

⚠️ **Important Security Notice:**

The browser's Geolocation API requires a **secure context** to function properly. This means:

- ✅ **HTTPS connections** (recommended for production)
- ✅ **localhost** (for development)
- ❌ **HTTP connections** will be blocked by modern browsers

### Error Handling

The package provides comprehensive error handling with user-friendly messages:

| Error Type               | Message                                                     | Description                                      |
| ------------------------ | ----------------------------------------------------------- | ------------------------------------------------ |
| **HTTPS Required**       | 🔒 Location services require a secure connection (HTTPS)    | Browser blocks geolocation on non-secure origins |
| **Permission Denied**    | 🚫 Location access denied. Please allow location permission | User explicitly denied location access           |
| **Position Unavailable** | 📍 Location information is unavailable                      | Device can't determine location                  |
| **Timeout**              | ⏱️ Location request timed out                               | Location request exceeded timeout limit          |

All errors are displayed in a prominent **red banner** below the location button with:

- 🎨 Enhanced styling with gradient background
- 📱 Responsive design for mobile devices
- ✨ Smooth slide-in animation
- 🔍 Clear iconography and user-friendly messages

### Development Setup

For local development, ensure you're using:

```bash
# Use localhost (secure context)
http://localhost:8000

# Or use Herd/Valet with HTTPS
https://your-app.test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Abhishek Sharma](https://github.com/TheAbhishekIN)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
