<?php

namespace TheAbhishekIN\FilamentLocation\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns\HasHelperText;
use Filament\Forms\Components\Concerns\HasHint;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class LocationPicker extends Field
{
    use HasHelperText;
    use HasHint;
    use HasExtraAlpineAttributes;

    protected string $view = 'filament-location::forms.components.location-picker';

    protected string | \Closure | null $latitudeField = null;

    protected string | \Closure | null $longitudeField = null;

    protected int | \Closure $zoom = 15;

    protected string | \Closure $height = '300px';

    protected bool | \Closure $showMap = true;

    protected bool | \Closure $showCoordinates = true;

    protected string | \Closure $mapType = 'standard';

    protected array | \Closure $mapControls = [];

    protected array | \Closure $initialLocation = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([
            'latitude' => null,
            'longitude' => null,
        ]);

        // Always save the component's own data
        $this->dehydrated(true);

        $this->afterStateUpdated(function (LocationPicker $component, $state) {
            // If separate lat/lng fields are configured, also update them
            $latitudeField = $component->getLatitudeField();
            $longitudeField = $component->getLongitudeField();

            if ($latitudeField && $longitudeField && is_array($state)) {
                $livewire = $component->getLivewire();

                // Set the individual latitude and longitude fields
                data_set($livewire, $latitudeField, $state['latitude'] ?? null);
                data_set($livewire, $longitudeField, $state['longitude'] ?? null);
            }
        });

        // Handle dehydration - save location as JSON
        $this->dehydrateStateUsing(function ($state) {
            if (is_array($state) && isset($state['latitude'], $state['longitude'])) {
                // Only save if we have valid coordinates
                if ($state['latitude'] && $state['longitude']) {
                    return [
                        'latitude' => (float) $state['latitude'],
                        'longitude' => (float) $state['longitude'],
                    ];
                }
            }
            return null;
        });

        // Handle hydration - load existing data on component load
        $this->afterStateHydrated(function (LocationPicker $component, $state) {
            // If we have separate lat/lng fields configured, try to load from them first
            $latitudeField = $component->getLatitudeField();
            $longitudeField = $component->getLongitudeField();

            if ($latitudeField && $longitudeField) {
                $livewire = $component->getLivewire();
                $latitude = data_get($livewire, $latitudeField);
                $longitude = data_get($livewire, $longitudeField);

                if ($latitude && $longitude) {
                    $component->state([
                        'latitude' => (float) $latitude,
                        'longitude' => (float) $longitude,
                    ]);
                    return;
                }
            }

            // If the state is already an array with coordinates, keep it
            if (is_array($state) && isset($state['latitude'], $state['longitude'])) {
                return;
            }

            // Try to decode JSON if it's a string
            if (is_string($state)) {
                $decoded = json_decode($state, true);
                if (is_array($decoded) && isset($decoded['latitude'], $decoded['longitude'])) {
                    $component->state([
                        'latitude' => (float) $decoded['latitude'],
                        'longitude' => (float) $decoded['longitude'],
                    ]);
                    return;
                }
            }

            // Set default empty state
            $component->state([
                'latitude' => null,
                'longitude' => null,
            ]);
        });
    }

    public function latitude(string | \Closure | null $field): static
    {
        $this->latitudeField = $field;

        return $this;
    }

    public function longitude(string | \Closure | null $field): static
    {
        $this->longitudeField = $field;

        return $this;
    }

    public function zoom(int | \Closure $zoom): static
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function height(string | \Closure $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function showMap(bool | \Closure $show = true): static
    {
        $this->showMap = $show;

        return $this;
    }

    public function showCoordinates(bool | \Closure $show = true): static
    {
        $this->showCoordinates = $show;

        return $this;
    }

    public function mapType(string | \Closure $type): static
    {
        $this->mapType = $type;

        return $this;
    }

    public function mapControls(array | \Closure $controls): static
    {
        $this->mapControls = $controls;

        return $this;
    }

    public function initialLocation(array | \Closure $location): static
    {
        $this->initialLocation = $location;

        return $this;
    }

    public function getLatitudeField(): ?string
    {
        return $this->evaluate($this->latitudeField);
    }

    public function getLongitudeField(): ?string
    {
        return $this->evaluate($this->longitudeField);
    }

    public function getZoom(): int
    {
        return $this->evaluate($this->zoom);
    }

    public function getHeight(): string
    {
        return $this->evaluate($this->height);
    }

    public function getShowMap(): bool
    {
        return $this->evaluate($this->showMap);
    }

    public function getShowCoordinates(): bool
    {
        return $this->evaluate($this->showCoordinates);
    }

    public function getMapType(): string
    {
        return $this->evaluate($this->mapType);
    }

    /**
     * @return array<string, bool>
     */
    public function getMapControls(): array
    {
        /** @var array<string, bool> $controls */
        $controls = $this->evaluate($this->mapControls);

        if (empty($controls)) {
            return config('filament-location.map_controls', [
                'zoom_control' => true,
                'map_type_control' => true,
                'scale_control' => true,
                'street_view_control' => true,
                'rotate_control' => true,
                'fullscreen_control' => true,
            ]);
        }

        return $controls;
    }

    /**
     * @return array{latitude: float|null, longitude: float|null}
     */
    public function getInitialLocation(): array
    {
        // Check explicitly set initial location first
        $initialLocation = $this->evaluate($this->initialLocation);
        if (is_array($initialLocation) && isset($initialLocation['latitude'], $initialLocation['longitude'])) {
            return [
                'latitude' => $initialLocation['latitude'] ? (float) $initialLocation['latitude'] : null,
                'longitude' => $initialLocation['longitude'] ? (float) $initialLocation['longitude'] : null,
            ];
        }

        // Check component's own state
        $state = $this->getState();
        if (is_array($state) && isset($state['latitude'], $state['longitude'])) {
            return [
                'latitude' => $state['latitude'] ? (float) $state['latitude'] : null,
                'longitude' => $state['longitude'] ? (float) $state['longitude'] : null,
            ];
        }

        // Fallback to separate fields if configured
        $latitudeField = $this->getLatitudeField();
        $longitudeField = $this->getLongitudeField();

        if ($latitudeField && $longitudeField) {
            $livewire = $this->getLivewire();
            $latitude = data_get($livewire, $latitudeField);
            $longitude = data_get($livewire, $longitudeField);

            if ($latitude && $longitude) {
                return [
                    'latitude' => (float) $latitude,
                    'longitude' => (float) $longitude,
                ];
            }
        }

        return [
            'latitude' => null,
            'longitude' => null,
        ];
    }
}
