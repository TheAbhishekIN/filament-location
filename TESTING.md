# Testing Guide

This document explains how to run and understand the test suite for the Filament Location package.

## Test Framework

This package uses **Pest PHP** for testing, which provides a delightful testing experience with an elegant syntax.

## Prerequisites

Make sure you have the testing dependencies installed:

```bash
composer install
```

## Running Tests

### Run All Tests

```bash
composer test
```

### Run Tests with Coverage

```bash
composer test-coverage
```

### Run Specific Test File

```bash
vendor/bin/pest tests/Feature/LocationPickerFeatureTest.php
```

### Run Tests with Verbose Output

```bash
vendor/bin/pest --verbose
```

## Test Structure

The test suite is organized into the following categories:

### Feature Tests

-   **LocationPickerFeatureTest.php** - Tests for the LocationPicker form component
-   **LocationColumnFeatureTest.php** - Tests for the LocationColumn table component

### Unit Tests

-   **TestCase.php** - Base test case with Filament setup
-   **HasLocationTest.php** - Tests for the HasLocation trait
-   **ServiceProviderTest.php** - Tests for service provider registration
-   **PackageIntegrationTest.php** - Integration tests

## Test Coverage

The test suite covers:

### LocationPicker Component

-   ✅ Component instantiation
-   ✅ Configuration methods (zoom, height, mapType)
-   ✅ State hydration/dehydration
-   ✅ Map controls configuration
-   ✅ Validation handling
-   ✅ Initial location setting
-   ✅ Coordinate display options

### LocationColumn Component

-   ✅ Component instantiation
-   ✅ Field configuration (latitude/longitude)
-   ✅ Icon types and customization
-   ✅ Location data extraction
-   ✅ Custom getters
-   ✅ Map controls
-   ✅ Tooltip configuration

### HasLocation Trait

-   ✅ Distance calculations
-   ✅ Google Maps URL generation
-   ✅ Coordinate validation
-   ✅ Null handling
-   ✅ Bearing calculations (if implemented)
-   ✅ Midpoint calculations (if implemented)

### Service Provider

-   ✅ Configuration loading
-   ✅ View registration
-   ✅ Asset publishing
-   ✅ Component registration

### Integration Tests

-   ✅ Form submission with location data
-   ✅ Table display of location data
-   ✅ Distance calculations between models
-   ✅ Legacy field support
-   ✅ Configuration accessibility

## Writing New Tests

### Feature Test Example

```php
<?php

use TheAbhishekIN\FilamentLocation\Forms\Components\LocationPicker;

it('can validate required location data', function () {
    $picker = LocationPicker::make('location')->required();

    $picker->state(null);
    expect($picker->hasValidationErrors(['required']))->toBeTrue();

    $picker->state(['latitude' => 26.9124, 'longitude' => 75.7873]);
    expect($picker->hasValidationErrors(['required']))->toBeFalse();
});
```

### Integration Test Example

```php
<?php

it('can save and retrieve location data', function () {
    $locationData = ['latitude' => 26.9124, 'longitude' => 75.7873];

    $record = TestModel::create(['location' => $locationData]);

    expect($record->location['latitude'])->toBe(26.9124);
    expect($record->location['longitude'])->toBe(75.7873);
});
```

## Test Configuration

### PHPUnit Configuration

The package includes a `phpunit.xml` file with:

-   SQLite in-memory database for testing
-   Test coverage reporting
-   Proper test discovery

### Pest Configuration

The `tests/Pest.php` file sets up:

-   Base test case binding
-   Custom expectations
-   Helper functions

## Continuous Integration

For CI/CD pipelines, use:

```yaml
- name: Run Tests
  run: composer test

- name: Run Coverage
  run: composer test-coverage
```

## Test Database

Tests use an in-memory SQLite database that is:

-   Created fresh for each test
-   Automatically migrated
-   Cleaned up after each test

## Mocking External Services

### Google Maps API

Tests mock the Google Maps API to avoid:

-   Rate limiting
-   Network dependencies
-   API key requirements

### Example Mock

```php
it('handles google maps api failure gracefully', function () {
    // Mock API failure
    Http::fake([
        'maps.googleapis.com/*' => Http::response([], 500)
    ]);

    $component = LocationPicker::make('location');
    // Test error handling
});
```

## Performance Testing

For performance-critical operations:

```php
it('calculates distance efficiently', function () {
    $start = microtime(true);

    $model = new TestModel();
    $distance = $model->calculateDistance(26.9124, 75.7873, 28.7041, 77.1025);

    $executionTime = microtime(true) - $start;
    expect($executionTime)->toBeLessThan(0.001); // 1ms
});
```

## Testing Best Practices

1. **Use descriptive test names** that explain what is being tested
2. **Test both success and failure scenarios**
3. **Mock external dependencies** to ensure reliable tests
4. **Use factories or fixtures** for consistent test data
5. **Test edge cases** like null values and boundary conditions
6. **Keep tests focused** on a single behavior
7. **Use arrange-act-assert** pattern

## Debugging Tests

### View Test Output

```bash
vendor/bin/pest --verbose --stop-on-failure
```

### Debug Specific Test

```php
it('debugs location calculation', function () {
    $model = new TestModel();
    $distance = $model->calculateDistance(26.9124, 75.7873, 28.7041, 77.1025);

    dump($distance); // Use dump() or dd() for debugging

    expect($distance)->toBeGreaterThan(270);
});
```

## Troubleshooting

### Common Issues

1. **Database Migration Errors**

    - Ensure test database configuration is correct
    - Check migration files for syntax errors

2. **Component Not Found**

    - Verify service provider is registered in TestCase
    - Check autoloading configuration

3. **Configuration Missing**
    - Ensure test environment sets required config values
    - Check config/filament-location.php exists

### Getting Help

If you encounter issues:

1. Check the test output for detailed error messages
2. Verify your environment meets the requirements
3. Review the existing test files for examples
4. Open an issue on GitHub with test failure details
