@php
    $id = $getId();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $zoom = $getZoom();
    $height = $getHeight();
    $showMap = $getShowMap();
    $showCoordinates = $getShowCoordinates();
    $mapType = $getMapType();
    $mapControls = $getMapControls();
    $initialLocation = $getInitialLocation();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="locationPicker({
            state: $wire.entangle('{{ $statePath }}'),
            zoom: {{ $zoom }},
            mapType: '{{ $mapType }}',
            showMap: {{ $showMap ? 'true' : 'false' }},
            showCoordinates: {{ $showCoordinates ? 'true' : 'false' }},
            mapControls: @js($mapControls),
            initialLocation: @js($initialLocation),
            isDisabled: {{ $isDisabled ? 'true' : 'false' }},
        })"
        x-init="init()"
        class="filament-location-picker"
    >
        <!-- Control Buttons -->
        <div class="mb-4 flex gap-2">
            <button
                type="button"
                x-on:click="getCurrentLocation()"
                x-bind:disabled="isDisabled || isLoading"
                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg x-show="!isLoading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <svg x-show="isLoading" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="isLoading ? 'Getting Location...' : 'Get Current Location'"></span>
            </button>

            <button
                type="button"
                x-on:click="clearLocation()"
                x-bind:disabled="isDisabled || !canClearLocation"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Clear Location
            </button>
        </div>

        <!-- Coordinates Display -->
        <div x-show="showCoordinates && hasLocation" class="mb-4 p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-600">
                <div class="flex items-center gap-4">
                    <div>
                        <span class="font-medium">Latitude:</span>
                        <span x-text="(hasValidState && state.latitude) ? state.latitude : 'Not set'"></span>
                    </div>
                    <div>
                        <span class="font-medium">Longitude:</span>
                        <span x-text="(hasValidState && state.longitude) ? state.longitude : 'Not set'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Display -->
        <div x-show="error" x-text="error" class="error-banner" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0"></div>

        <!-- Map Container -->
        <div 
            x-show="showMap && hasLocation"
            x-ref="mapContainer"
            class="w-full rounded-lg border border-gray-300 overflow-hidden"
            style="height: {{ $height }}"
        ></div>

        <!-- No Location Message -->
        <div x-show="showMap && !hasLocation" class="w-full p-8 text-center bg-gray-50 rounded-lg border border-gray-300" style="height: {{ $height }}">
            <div class="flex flex-col items-center justify-center h-full">
                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <p class="text-gray-500">Click "Get Current Location" to show map</p>
            </div>
        </div>
    </div>
</x-dynamic-component> 