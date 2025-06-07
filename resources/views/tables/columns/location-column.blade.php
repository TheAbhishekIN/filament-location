@php
    $locationData = $getLocationData($getRecord());
    $hasLocation = $locationData['hasLocation'];
    $latitude = $locationData['latitude'];
    $longitude = $locationData['longitude'];
    $showTooltip = $getShowTooltip();
    $tooltipText = $getTooltipText();
    $iconSize = $getIconSize();
    $iconColor = $getIconColor();
    $zoom = $getZoom();
    $height = $getHeight();
    $mapType = $getMapType();
    $mapControls = $getMapControls();
    $title = $getTitle();
    $customIcon = $getCustomIcon();
    $useCustomIcon = $getUseCustomIcon();
    
    // Debug: check values
    // dd([
    //     'useCustomIcon' => $useCustomIcon,
    //     'customIcon' => $customIcon,
    //     'title' => $title
    // ]);
@endphp

<div class="filament-location-column">
    @if($hasLocation)
        <div
            x-data="locationColumn({
                latitude: {{ $latitude }},
                longitude: {{ $longitude }},
                zoom: {{ $zoom }},
                height: '{{ $height }}',
                mapType: '{{ $mapType }}',
                mapControls: @js($mapControls),
            })"
            class="inline-block"
            @click.stop=""
        >
            <button
                type="button"
                x-on:click.stop="openLocationModal()"
                @if($showTooltip)
                    x-tooltip="'{{ $tooltipText }}'"
                @endif
                class="inline-flex items-center justify-center transition-colors duration-200 hover:bg-gray-100 rounded-full p-1 
                @switch($iconSize)
                    @case('sm')
                        w-6 h-6
                        @break
                    @case('md')
                        w-8 h-8
                        @break
                    @case('lg')
                        w-10 h-10
                        @break
                    @case('xl')
                        w-12 h-12
                        @break
                    @default
                        w-8 h-8
                @endswitch
                "
            >
                @if($useCustomIcon && $customIcon)
                    <svg 
                        class="
                        @switch($iconSize)
                            @case('sm')
                                w-4 h-4
                                @break
                            @case('md')
                                w-5 h-5
                                @break
                            @case('lg')
                                w-6 h-6
                                @break
                            @case('xl')
                                w-8 h-8
                                @break
                            @default
                                w-5 h-5
                        @endswitch
                        " 
                        viewBox="0 0 24 24"
                    >
                        {!! $customIcon !!}
                    </svg>
                @else
                    {{-- Default icon with dynamic colors based on iconColor --}}
                    <svg 
                        class="
                        @switch($iconSize)
                            @case('sm')
                                w-4 h-4
                                @break
                            @case('md')
                                w-5 h-5
                                @break
                            @case('lg')
                                w-6 h-6
                                @break
                            @case('xl')
                                w-8 h-8
                                @break
                            @default
                                w-5 h-5
                        @endswitch
                        " 
                        fill="@switch($iconColor)
                            @case('success')
                                #22c55e
                                @break
                            @case('warning')
                                #f59e0b
                                @break
                            @case('primary')
                                #3b82f6
                                @break
                            @case('danger')
                                #ef4444
                                @break
                            @default
                                #6b7280
                        @endswitch" 
                        viewBox="0 0 24 24"
                    >
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        <circle cx="12" cy="9" r="1.5" fill="#ffffff"/>
                    </svg>
                @endif
            </button>

            <!-- Location Modal -->
            <div
                x-show="showModal"
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
                style="display: none;"
            >
                <div
                    x-show="showModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    x-on:click.away="closeLocationModal()"
                    class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden"
                >
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                        <button
                            type="button"
                            x-on:click="closeLocationModal()"
                            class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-4">
                        <!-- Coordinates Display -->
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <div class="text-sm text-gray-600">
                                <div class="flex items-center gap-6">
                                    <div>
                                        <span class="font-medium">Latitude:</span>
                                        <span class="ml-1">{{ $latitude }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Longitude:</span>
                                        <span class="ml-1">{{ $longitude }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Map Container -->
                        <div 
                            x-ref="modalMapContainer"
                            class="w-full rounded-lg border border-gray-300 overflow-hidden"
                            style="height: {{ $height }}"
                        ></div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-between gap-3 p-4 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            Click and drag to view different areas
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                x-on:click="openInGoogleMaps()"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                                Open in Google Maps
                            </button>
                            <button
                                type="button"
                                x-on:click="closeLocationModal()"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="flex items-center text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"></path>
            </svg>
            <span class="ml-1 text-sm">No location</span>
        </div>
    @endif
</div> 