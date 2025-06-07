<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Maps API Key
    |--------------------------------------------------------------------------
    |
    | Your Google Maps API key. You can get one from the Google Cloud Console.
    | Make sure to enable the Maps JavaScript API and Geocoding API.
    |
    */
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Map Zoom Level
    |--------------------------------------------------------------------------
    |
    | The default zoom level for maps displayed in the package.
    | Range: 1 (world view) to 20 (street level)
    |
    */
    'default_zoom' => 15,

    /*
    |--------------------------------------------------------------------------
    | Map Height
    |--------------------------------------------------------------------------
    |
    | The default height for map containers in the package.
    |
    */
    'map_height' => '400px',

    /*
    |--------------------------------------------------------------------------
    | Enable Street View
    |--------------------------------------------------------------------------
    |
    | Whether to enable Street View functionality in the maps.
    |
    */
    'enable_street_view' => true,

    /*
    |--------------------------------------------------------------------------
    | Map Theme
    |--------------------------------------------------------------------------
    |
    | The default map theme/style. Options: 'standard', 'satellite', 'hybrid', 'terrain'
    |
    */
    'map_type' => 'standard',

    /*
    |--------------------------------------------------------------------------
    | Location Accuracy
    |--------------------------------------------------------------------------
    |
    | The desired accuracy of the location in meters.
    | Lower values may take longer to acquire location.
    |
    */
    'location_accuracy' => 100,

    /*
    |--------------------------------------------------------------------------
    | Location Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time to wait for location in milliseconds.
    |
    */
    'location_timeout' => 10000,

    /*
    |--------------------------------------------------------------------------
    | Enable High Accuracy
    |--------------------------------------------------------------------------
    |
    | Whether to enable high accuracy location detection (uses GPS if available).
    |
    */
    'enable_high_accuracy' => true,

    /*
    |--------------------------------------------------------------------------
    | Map Controls
    |--------------------------------------------------------------------------
    |
    | Configure which map controls to show.
    |
    */
    'map_controls' => [
        'zoom_control' => true,
        'map_type_control' => true,
        'scale_control' => true,
        'street_view_control' => true,
        'rotate_control' => true,
        'fullscreen_control' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Map Styles
    |--------------------------------------------------------------------------
    |
    | Custom JSON map styles for Google Maps.
    | Leave empty for default styling.
    |
    */
    'custom_map_styles' => [],

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    |
    | The language to use for map labels and controls.
    |
    */
    'language' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Region
    |--------------------------------------------------------------------------
    |
    | The region code to use for map biasing.
    |
    */
    'region' => 'US',
];
