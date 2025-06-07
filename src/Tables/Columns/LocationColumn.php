<?php

namespace TheAbhishekIN\FilamentLocation\Tables\Columns;

use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns\CanBeSearchable;
use Filament\Tables\Columns\Concerns\CanBeSortable;

class LocationColumn extends Column
{
    use CanBeSearchable;
    use CanBeSortable;

    protected string $view = 'filament-location::tables.columns.location-column';

    protected string | \Closure | null $latitudeField = null;

    protected string | \Closure | null $longitudeField = null;

    protected int | \Closure $zoom = 15;

    protected string | \Closure $height = '400px';

    protected string | \Closure $mapType = 'standard';

    protected array | \Closure $mapControls = [];

    protected bool | \Closure $showTooltip = true;

    protected string | \Closure $tooltipText = 'Click to view location on map';

    protected string | \Closure $iconSize = 'lg';

    protected string | \Closure $iconColor = 'primary';

    protected \Closure | null $getLatitudeUsing = null;

    protected \Closure | null $getLongitudeUsing = null;

    protected string | \Closure | null $title = null;

    protected string | \Closure | null $customIcon = null;

    protected bool | \Closure $useCustomIcon = false;

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

    public function showTooltip(bool | \Closure $show = true): static
    {
        $this->showTooltip = $show;

        return $this;
    }

    public function tooltipText(string | \Closure $text): static
    {
        $this->tooltipText = $text;

        return $this;
    }

    public function iconSize(string | \Closure $size): static
    {
        $this->iconSize = $size;

        return $this;
    }

    public function iconColor(string | \Closure $color): static
    {
        $this->iconColor = $color;

        return $this;
    }

    public function getLatitudeUsing(\Closure $callback): static
    {
        $this->getLatitudeUsing = $callback;

        return $this;
    }

    public function getLongitudeUsing(\Closure $callback): static
    {
        $this->getLongitudeUsing = $callback;

        return $this;
    }

    public function title(string | \Closure | null $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function customIcon(string | \Closure | null $icon): static
    {
        $this->customIcon = $icon;
        $this->useCustomIcon = true;

        return $this;
    }

    public function iconType(string | \Closure $type): static
    {
        $evaluatedType = $this->evaluate($type);
        $this->customIcon = $this->getIconSvg($evaluatedType);
        $this->useCustomIcon = true;

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

    public function getMapType(): string
    {
        return $this->evaluate($this->mapType);
    }

    /**
     * @return array<string, bool>
     */
    public function getMapControls(): array
    {
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

    public function getShowTooltip(): bool
    {
        return $this->evaluate($this->showTooltip);
    }

    public function getTooltipText(): string
    {
        return $this->evaluate($this->tooltipText);
    }

    public function getIconSize(): string
    {
        return $this->evaluate($this->iconSize);
    }

    public function getIconColor(): string
    {
        return $this->evaluate($this->iconColor);
    }

    public function getTitle(): ?string
    {
        return $this->evaluate($this->title);
    }

    public function getCustomIcon(): ?string
    {
        return $this->evaluate($this->customIcon);
    }

    public function getUseCustomIcon(): bool
    {
        return $this->evaluate($this->useCustomIcon);
    }

    /**
     * Get predefined icon SVG by type
     */
    protected function getIconSvg(string $type): string
    {
        return match ($type) {
            'check-in' => '<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#22c55e"/><circle cx="12" cy="9" r="1.5" fill="#ffffff"/>',
            'check-out' => '<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#f59e0b"/><circle cx="12" cy="9" r="1.5" fill="#ffffff"/>',
            'home' => '<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="#3b82f6"/>',
            'office' => '<path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z" fill="#6366f1"/>',
            'travel' => '<path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z" fill="#8b5cf6"/>',
            default => '<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>'
        };
    }

    /**
     * @param mixed $record
     * @return array{latitude: float|null, longitude: float|null, hasLocation: bool}
     */
    public function getLocationData($record): array
    {
        $latitude = null;
        $longitude = null;

        // Use custom getters if provided
        if ($this->getLatitudeUsing) {
            $latitude = call_user_func($this->getLatitudeUsing, $record);
        } elseif ($latitudeField = $this->getLatitudeField()) {
            $latitude = data_get($record, $latitudeField);
        }

        if ($this->getLongitudeUsing) {
            $longitude = call_user_func($this->getLongitudeUsing, $record);
        } elseif ($longitudeField = $this->getLongitudeField()) {
            $longitude = data_get($record, $longitudeField);
        }

        return [
            'latitude' => $latitude ? (float) $latitude : null,
            'longitude' => $longitude ? (float) $longitude : null,
            'hasLocation' => $latitude && $longitude,
        ];
    }

    /**
     * @param mixed $record
     */
    public function hasLocation($record): bool
    {
        $locationData = $this->getLocationData($record);

        return $locationData['hasLocation'];
    }
}
