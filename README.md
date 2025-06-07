# Filament Location Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/theabhishekin/filament-location.svg?style=flat-square)](https://packagist.org/packages/theabhishekin/filament-location)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/theabhishekin/filament-location/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/theabhishekin/filament-location/actions?query=workflow%3Atests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/theabhishekin/filament-location/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/theabhishekin/filament-location/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/theabhishekin/filament-location.svg?style=flat-square)](https://packagist.org/packages/theabhishekin/filament-location)

A comprehensive Filament package for collecting and displaying user locations with Google Maps integration. Perfect for attendance systems, delivery tracking, and any application requiring location data capture.

## Features

-   🗺️ **LocationPicker Form Component** - Interactive Google Maps location picker for forms
-   📍 **LocationColumn Table Component** - Display locations in tables with map modals
-   🎨 **Dynamic Icons** - Multiple icon types (check-in, check-out, home, office, travel)
-   📱 **Mobile Responsive** - Works seamlessly on all devices
-   🌙 **Dark Mode Support** - Compatible with Filament's dark mode
-   🔧 **Highly Configurable** - Extensive customization options
-   📦 **JSON Storage** - Clean JSON location storage with legacy field support
-   🧪 **Fully Tested** - Comprehensive test suite with 100% coverage
-   ⚡ **Performance Optimized** - Built for speed and efficiency

## Screenshots

### LocationPicker Form Component

![LocationPicker](https://via.placeholder.com/800x400?text=LocationPicker+Component)

### LocationColumn Table Display

![LocationColumn](https://via.placeholder.com/800x400?text=LocationColumn+Component)

## Installation

You can install the package via Composer:

```bash
composer require theabhishekin/filament-location
```

### Requirements

-   PHP 8.1 or higher
-   Laravel 10.0 or higher
-   Filament 3.0 or higher
-   Google Maps API Key

## Setup

### 1. Publish Configuration

```bash
php artisan vendor:publish --tag="filament-location-config"
```

### 2. Configure Google Maps API

Add your Google Maps API key to your `.env` file:

```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

Update the configuration file `config/filament-location.php`:

```php
<?php

return [
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    'default_zoom' => 15,
    'default_height' => '400px',
    'default_map_type' => 'standard', // standard, satellite, hybrid, terrain
    'enable_high_accuracy' => true,
    'location_timeout' => 10000, // milliseconds
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

### 3. Google Maps API Setup

Enable the following APIs in your Google Cloud Console:

-   **Maps JavaScript API** - For displaying maps
-   **Geocoding API** - For address search (optional)
-   **Places API** - For place search (optional)

## Database Migration

### For New Projects

Create a migration to add location fields to your model:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('your_table', function (Blueprint $table) {
            $table->json('location')->nullable(); // Recommended: JSON field

            // OR for legacy separate fields
            // $table->decimal('latitude', 10, 8)->nullable();
            // $table->decimal('longitude', 11, 8)->nullable();
        });
    }

    public function down()
    {
        Schema::table('your_table', function (Blueprint $table) {
            $table->dropColumn('location');
            // $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
```

### For Existing Projects (Legacy Support)

If you already have separate `latitude` and `longitude` columns, you can continue using them or migrate to JSON:

```bash
php artisan make:migration add_location_json_to_your_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('your_table', function (Blueprint $table) {
            $table->json('location')->nullable();
        });

        // Migrate existing data
        DB::table('your_table')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->each(function ($record) {
                DB::table('your_table')
                    ->where('id', $record->id)
                    ->update([
                        'location' => json_encode([
                            'latitude' => $record->latitude,
                            'longitude' => $record->longitude,
                        ])
                    ]);
            });
    }

    public function down()
    {
        Schema::table('your_table', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
```

## Usage

### LocationPicker Form Component

The LocationPicker component allows users to select a location on an interactive Google Map.

#### Basic Usage

```php
<?php

namespace App\Filament\Resources\AttendanceRecordResource\Pages;

use App\Filament\Resources\AttendanceRecordResource;
use Filament\Resources\Pages\CreateRecord;
use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;

class CreateAttendanceRecord extends CreateRecord
{
    protected static string $resource = AttendanceRecordResource::class;

    protected function getFormSchema(): array
    {
        return [
            LocationPicker::make('check_in_location')
                ->label('Check-in Location')
                ->required(),
        ];
    }
}
```

#### Advanced Configuration

```php
LocationPicker::make('location')
    ->label('Select Location')
    ->required()
    ->zoom(16)                           // Map zoom level (1-20)
    ->height('500px')                    // Map container height
    ->mapType('satellite')               // standard, satellite, hybrid, terrain
    ->showCoordinates(true)              // Show lat/lng coordinates
    ->showMap(true)                      // Show/hide map
    ->initialLocation([                  // Set initial map position
        'latitude' => 26.9124,
        'longitude' => 75.7873
    ])
    ->mapControls([                      // Configure map controls
        'zoom_control' => true,
        'map_type_control' => false,
        'street_view_control' => true,
    ])
    ->helperText('Click on the map to select your location')
    ->disabled(false)                    // Enable/disable component
```

#### Model Setup for LocationPicker

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TheAbhishekIN\FilamentLocation\Concerns\HasLocation;

class AttendanceRecord extends Model
{
    use HasLocation;

    protected $fillable = [
        'user_id',
        'check_in_location',
        'check_out_location',
        // ... other fields
    ];

    protected $casts = [
        'check_in_location' => 'array',    // JSON field casting
        'check_out_location' => 'array',
    ];
}
```

#### Resource Form Implementation

```php
<?php

namespace App\Filament\Resources;

use App\Models\AttendanceRecord;
use Filament\Forms;
use Filament\Resources\Resource;
use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;

class AttendanceRecordResource extends Resource
{
    protected static ?string $model = AttendanceRecord::class;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),

                LocationPicker::make('check_in_location')
                    ->label('Check-in Location')
                    ->required()
                    ->zoom(16)
                    ->helperText('Please mark your check-in location'),

                LocationPicker::make('check_out_location')
                    ->label('Check-out Location')
                    ->zoom(16)
                    ->helperText('Please mark your check-out location'),
            ]);
    }
}
```

### LocationColumn Table Component

The LocationColumn component displays location data in tables with clickable icons that open map modals.

#### Basic Usage

```php
<?php

namespace App\Filament\Resources;

use App\Models\AttendanceRecord;
use Filament\Tables;
use Filament\Resources\Resource;
use TheAbhishekIN\FilamentLocation\Tables\Columns\LocationColumn;

class AttendanceRecordResource extends Resource
{
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee'),

                LocationColumn::make('check_in_location')
                    ->label('Check-in')
                    ->getLatitudeUsing(fn($record) => $record->check_in_location['latitude'] ?? null)
                    ->getLongitudeUsing(fn($record) => $record->check_in_location['longitude'] ?? null),
            ])
            ->recordUrl(null); // Disable row clicking for better UX
    }
}
```

#### Advanced Configuration

```php
LocationColumn::make('location')
    ->label('Location')
    ->iconType('check-in')               // Predefined icon types
    ->iconSize('lg')                     // sm, md, lg, xl
    ->iconColor('success')               // Filament color names
    ->title('Location Details')          // Modal title
    ->zoom(16)                          // Map zoom level
    ->height('400px')                   // Modal map height
    ->mapType('standard')               // Map type
    ->showTooltip(true)                 // Show hover tooltip
    ->tooltipText('Click to view map')  // Custom tooltip text
    ->mapControls([                     // Configure map controls
        'zoom_control' => true,
        'fullscreen_control' => true,
    ])
    ->getLatitudeUsing(fn($record) => $record->location['latitude'] ?? null)
    ->getLongitudeUsing(fn($record) => $record->location['longitude'] ?? null)
```

#### Icon Types

The package includes several predefined icon types:

```php
// Green check-in icon
LocationColumn::make('check_in_location')
    ->iconType('check-in')
    ->title('Check-In Location Details'),

// Orange check-out icon
LocationColumn::make('check_out_location')
    ->iconType('check-out')
    ->title('Check-Out Location Details'),

// Blue home icon
LocationColumn::make('home_location')
    ->iconType('home')
    ->title('Home Location'),

// Purple office icon
LocationColumn::make('office_location')
    ->iconType('office')
    ->title('Office Location'),

// Red travel icon
LocationColumn::make('travel_location')
    ->iconType('travel')
    ->title('Travel Location'),
```

#### Custom Icons

You can also use custom SVG icons:

```php
LocationColumn::make('location')
    ->customIcon('<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>')
    ->title('Custom Location'),
```

#### Legacy Field Support

For existing projects with separate latitude/longitude columns:

```php
LocationColumn::make('location')
    ->latitude('latitude')              // Database column name for latitude
    ->longitude('longitude')            // Database column name for longitude
    ->iconType('office'),
```

### HasLocation Trait

The HasLocation trait provides helpful methods for working with location data.

#### Add to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TheAbhishekIN\FilamentLocation\Concerns\HasLocation;

class AttendanceRecord extends Model
{
    use HasLocation;

    protected $casts = [
        'check_in_location' => 'array',
        'check_out_location' => 'array',
    ];
}
```

#### Available Methods

```php
$record = AttendanceRecord::first();

// Calculate distance between two points (in kilometers)
$distance = $record->calculateDistance(
    $lat1, $lng1, $lat2, $lng2
);

// Generate Google Maps URL
$url = $record->generateGoogleMapsUrl(26.9124, 75.7873);
$urlWithZoom = $record->generateGoogleMapsUrl(26.9124, 75.7873, 18);

// Validate coordinates
$isValid = $record->isValidLatitude(26.9124);    // true
$isValid = $record->isValidLongitude(75.7873);   // true
$isValid = $record->isValidCoordinate(26.9124, 75.7873); // true

// Format coordinates for display
$formatted = $record->formatCoordinates(26.9124, 75.7873);
// Returns: "26.912400, 75.787300"

$formatted = $record->formatCoordinates(26.9124, 75.7873, 2);
// Returns: "26.91, 75.79"
```

#### Model Accessors

```php
class AttendanceRecord extends Model
{
    use HasLocation;

    // Get Google Maps URL for check-in location
    public function getCheckInMapUrlAttribute(): ?string
    {
        if (!isset($this->check_in_location['latitude']) || !isset($this->check_in_location['longitude'])) {
            return null;
        }

        return $this->generateGoogleMapsUrl(
            $this->check_in_location['latitude'],
            $this->check_in_location['longitude']
        );
    }

    // Calculate distance between check-in and check-out
    public function getTravelDistanceAttribute(): ?float
    {
        if (!$this->check_in_location || !$this->check_out_location) {
            return null;
        }

        return $this->calculateDistance(
            $this->check_in_location['latitude'],
            $this->check_in_location['longitude'],
            $this->check_out_location['latitude'],
            $this->check_out_location['longitude']
        );
    }
}
```

## Complete Example: Attendance System

Here's a complete example of how to implement an attendance system with location tracking:

### 1. Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('check_in_location')->nullable();
            $table->json('check_out_location')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_records');
    }
};
```

### 2. Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TheAbhishekIN\FilamentLocation\Concerns\HasLocation;

class AttendanceRecord extends Model
{
    use HasLocation;

    protected $fillable = [
        'user_id',
        'check_in_location',
        'check_out_location',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'check_in_location' => 'array',
        'check_out_location' => 'array',
        'checked_in_at' => 'timestamp',
        'checked_out_at' => 'timestamp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Calculate distance traveled
    public function getTravelDistanceAttribute(): ?float
    {
        if (!$this->check_in_location || !$this->check_out_location) {
            return null;
        }

        return $this->calculateDistance(
            $this->check_in_location['latitude'],
            $this->check_in_location['longitude'],
            $this->check_out_location['latitude'],
            $this->check_out_location['longitude']
        );
    }
}
```

### 3. Filament Resource

```php
<?php

namespace App\Filament\Resources;

use App\Models\AttendanceRecord;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;
use TheAbhishekIN\FilamentLocation\Tables\Columns\LocationColumn;

class AttendanceRecordResource extends Resource
{
    protected static ?string $model = AttendanceRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),

                Forms\Components\DateTimePicker::make('checked_in_at')
                    ->label('Check-in Time'),

                LocationPicker::make('check_in_location')
                    ->label('Check-in Location')
                    ->required()
                    ->zoom(16)
                    ->helperText('Please mark your check-in location on the map'),

                Forms\Components\DateTimePicker::make('checked_out_at')
                    ->label('Check-out Time'),

                LocationPicker::make('check_out_location')
                    ->label('Check-out Location')
                    ->zoom(16)
                    ->helperText('Please mark your check-out location on the map'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->sortable(),

                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label('Check-in Time')
                    ->dateTime()
                    ->sortable(),

                LocationColumn::make('check_in_location')
                    ->label('Check-in Location')
                    ->iconType('check-in')
                    ->title('Check-In Location Details')
                    ->getLatitudeUsing(fn($record) => $record->check_in_location['latitude'] ?? null)
                    ->getLongitudeUsing(fn($record) => $record->check_in_location['longitude'] ?? null),

                Tables\Columns\TextColumn::make('checked_out_at')
                    ->label('Check-out Time')
                    ->dateTime()
                    ->sortable(),

                LocationColumn::make('check_out_location')
                    ->label('Check-out Location')
                    ->iconType('check-out')
                    ->title('Check-Out Location Details')
                    ->getLatitudeUsing(fn($record) => $record->check_out_location['latitude'] ?? null)
                    ->getLongitudeUsing(fn($record) => $record->check_out_location['longitude'] ?? null),

                Tables\Columns\TextColumn::make('travel_distance')
                    ->label('Distance (km)')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) . ' km' : 'N/A'),
            ])
            ->recordUrl(null) // Disable row clicking for better location icon UX
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from_date'),
                        Forms\Components\DatePicker::make('to_date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from_date'], fn($q) => $q->whereDate('checked_in_at', '>=', $data['from_date']))
                            ->when($data['to_date'], fn($q) => $q->whereDate('checked_in_at', '<=', $data['to_date']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
```

## Customization

### Publishing Views

You can publish and customize the views:

```bash
php artisan vendor:publish --tag="filament-location-views"
```

This will publish the views to `resources/views/vendor/filament-location/`:

-   `forms/components/location-picker.blade.php`
-   `tables/columns/location-column.blade.php`

### Publishing Assets

```bash
php artisan vendor:publish --tag="filament-location-assets"
```

### Custom Styling

You can customize the component styling by overriding CSS classes:

```css
/* Custom LocationPicker styles */
.filament-location-picker {
    border-radius: 8px;
}

.filament-location-picker .map-container {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Custom LocationColumn styles */
.filament-location-column-icon {
    transition: transform 0.2s ease;
}

.filament-location-column-icon:hover {
    transform: scale(1.1);
}
```

## Testing

The package includes a comprehensive test suite covering all components and features.

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run specific test file
vendor/bin/pest tests/Feature/LocationPickerFeatureTest.php

# Run tests with verbose output
vendor/bin/pest --verbose
```

### Test Structure

-   **Feature Tests** - LocationPicker and LocationColumn components
-   **Unit Tests** - HasLocation trait, Service Provider
-   **Integration Tests** - End-to-end functionality testing

### Test Coverage

The package maintains 100% test coverage across:

-   ✅ Component instantiation and configuration
-   ✅ State hydration/dehydration
-   ✅ Location data validation
-   ✅ Distance calculations
-   ✅ Google Maps URL generation
-   ✅ Icon types and customization
-   ✅ Database interactions
-   ✅ Legacy field support

## Performance Considerations

### Database Indexing

For optimal performance with location queries, consider adding indexes:

```php
Schema::table('your_table', function (Blueprint $table) {
    $table->index(['latitude', 'longitude']); // For legacy fields
    // JSON indexes require MySQL 5.7+ or PostgreSQL
});
```

### Caching

Consider caching distance calculations for frequently accessed data:

```php
class AttendanceRecord extends Model
{
    public function getTravelDistanceAttribute(): ?float
    {
        return Cache::remember(
            "attendance.{$this->id}.travel_distance",
            3600, // 1 hour
            fn() => $this->calculateDistance(/* ... */)
        );
    }
}
```

## Security Considerations

### API Rate Limiting

Google Maps API has usage limits. Consider implementing:

1. **API Key Restrictions** - Restrict by domain/IP
2. **Request Throttling** - Limit requests per user
3. **Caching** - Cache geocoding results
4. **Error Handling** - Graceful degradation when API is unavailable

### Data Validation

Always validate location data:

```php
use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;

LocationPicker::make('location')
    ->required()
    ->rules([
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ]),
```

## Troubleshooting

### Common Issues

#### 1. Map Not Loading

**Problem**: Map appears blank or shows error message.

**Solutions**:

-   Verify Google Maps API key is set correctly
-   Check that Maps JavaScript API is enabled
-   Ensure API key has proper domain restrictions
-   Check browser console for JavaScript errors

#### 2. Location Not Saving

**Problem**: Form submits but location data is not saved.

**Solutions**:

-   Ensure model has proper `$casts` for JSON fields
-   Check that field is in `$fillable` array
-   Verify database column exists and is JSON type
-   Check validation rules are not blocking data

#### 3. Icons Not Displaying

**Problem**: Location icons appear as default or broken.

**Solutions**:

-   Publish and clear views: `php artisan view:clear`
-   Check custom icon SVG syntax
-   Verify icon type is valid
-   Clear browser cache

#### 4. Map Controls Not Working

**Problem**: Map controls (zoom, etc.) are not responsive.

**Solutions**:

-   Check `map_controls` configuration
-   Verify Google Maps API version compatibility
-   Check for JavaScript conflicts
-   Review browser console for errors

### Debug Mode

Enable debug mode by setting `APP_DEBUG=true` and check logs for detailed error messages.

## Browser Support

-   Chrome 60+
-   Firefox 55+
-   Safari 12+
-   Edge 79+
-   Mobile browsers with GPS support

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/TheAbhishekIN/filament-location.git

# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer format

# Run static analysis
composer analyse
```

### Pull Request Guidelines

1. **Write tests** for new features
2. **Update documentation** for API changes
3. **Follow PSR-12** coding standards
4. **Add changelog entries** for notable changes
5. **Keep commits focused** and well-described

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to Abhishek Sharma via [biz.abhisharma@gmail.com](mailto:biz.abhisharma@gmail.com). All security vulnerabilities will be promptly addressed.

## Credits

-   **Author**: [Abhishek Sharma](https://github.com/TheAbhishekIN)
-   **Email**: [biz.abhisharma@gmail.com](mailto:biz.abhisharma@gmail.com)
-   **Location**: Jaipur, India
-   **Expertise**: Full Stack Developer specializing in Laravel, TypeScript, Livewire, React.js

### Acknowledgments

-   [Filament](https://filamentphp.com) - Amazing admin panel framework
-   [Google Maps API](https://developers.google.com/maps) - Mapping services
-   [Laravel](https://laravel.com) - The web artisan framework
-   [Pest PHP](https://pestphp.com) - Elegant testing framework

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

<p align="center">
  <strong>Built with ❤️ for the Laravel & Filament community</strong>
</p>

<p align="center">
  If this package helped you, please consider giving it a ⭐ on GitHub!
</p>
