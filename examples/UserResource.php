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

/**
 * User Resource Example demonstrating JSON Location Support
 * 
 * This example shows:
 * - JSON location storage (recommended)
 * - Multiple location types (current, home, office)
 * - Dynamic icons and styling
 * - Advanced LocationPicker configuration
 * - LocationColumn with tooltips and modals
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location Information')
                    ->schema([
                        // Current Location
                        LocationPicker::make('location')
                            ->label('Current Location')
                            ->required()
                            ->zoom(16)                           // Map zoom level (1-20)
                            ->height('400px')                   // Map container height
                            ->mapType('standard')               // standard, satellite, hybrid, terrain
                            ->showCoordinates(true)             // Show lat/lng coordinates
                            ->showMap(true)                     // Show/hide map
                            ->initialLocation([                 // Set initial map position (optional)
                                'latitude' => 26.9124,          // Jaipur coordinates
                                'longitude' => 75.7873
                            ])
                            ->mapControls([                     // Configure map controls
                                'zoom_control' => true,
                                'map_type_control' => true,
                                'scale_control' => true,
                                'street_view_control' => true,
                                'rotate_control' => true,
                                'fullscreen_control' => true,
                            ])
                            ->helperText('Click on the map to select your current location')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('location_updated_at')
                            ->label('Location Updated At')
                            ->disabled()
                            ->placeholder('Will be set automatically')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Additional Locations')
                    ->schema([
                        LocationPicker::make('home_location')
                            ->label('Home Location')
                            ->zoom(16)
                            ->height('300px')
                            ->mapType('standard')
                            ->showCoordinates(false)            // Hide coordinates for cleaner UI
                            ->helperText('Mark your home location'),

                        LocationPicker::make('office_location')
                            ->label('Office Location')
                            ->zoom(16)
                            ->height('300px')
                            ->mapType('standard')
                            ->showCoordinates(false)
                            ->helperText('Mark your office location'),
                    ])
                    ->columns(1)
                    ->collapsible(),
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

                // Current Location Column
                LocationColumn::make('location')
                    ->label('Current Location')
                    ->iconType('home')                       // Use home icon
                    ->iconSize('lg')                         // Large icon size
                    ->iconColor('primary')                   // Primary color
                    ->title('User Location Details')         // Modal title
                    ->zoom(16)                              // Map zoom in modal
                    ->height('400px')                       // Modal map height
                    ->mapType('standard')                   // Map type in modal
                    ->showTooltip(true)                     // Show hover tooltip
                    ->tooltipText('View user location')      // Custom tooltip text
                    ->getLatitudeUsing(fn($record) => $record->location['latitude'] ?? null)
                    ->getLongitudeUsing(fn($record) => $record->location['longitude'] ?? null),

                // Home Location Column
                LocationColumn::make('home_location')
                    ->label('Home')
                    ->iconType('home')                       // Home icon
                    ->iconSize('sm')
                    ->iconColor('info')
                    ->title('Home Location')
                    ->getLatitudeUsing(fn($record) => $record->home_location['latitude'] ?? null)
                    ->getLongitudeUsing(fn($record) => $record->home_location['longitude'] ?? null),

                // Office Location Column
                LocationColumn::make('office_location')
                    ->label('Office')
                    ->iconType('office')                     // Office icon
                    ->iconSize('sm')
                    ->iconColor('warning')
                    ->title('Office Location')
                    ->getLatitudeUsing(fn($record) => $record->office_location['latitude'] ?? null)
                    ->getLongitudeUsing(fn($record) => $record->office_location['longitude'] ?? null),

                Tables\Columns\TextColumn::make('location_updated_at')
                    ->label('Location Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(null) // Disable row clicking for better location icon UX
            ->filters([
                // Location-based filters
                Tables\Filters\Filter::make('has_location')
                    ->label('Has Current Location')
                    ->query(fn($query) => $query->whereNotNull('location'))
                    ->toggle(),

                Tables\Filters\Filter::make('has_home_location')
                    ->label('Has Home Location')
                    ->query(fn($query) => $query->whereNotNull('home_location'))
                    ->toggle(),

                Tables\Filters\Filter::make('has_office_location')
                    ->label('Has Office Location')
                    ->query(fn($query) => $query->whereNotNull('office_location'))
                    ->toggle(),

                Tables\Filters\Filter::make('verified_users')
                    ->label('Verified Users')
                    ->query(fn($query) => $query->whereNotNull('email_verified_at'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('view_all_locations')
                    ->label('View All Locations')
                    ->icon('heroicon-o-map')
                    ->color('info')
                    ->visible(fn($record) => $record->location || $record->home_location || $record->office_location)
                    ->action(function ($record) {
                        // You can implement a comprehensive location view here
                        // Maybe open a modal with all location data
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('export_locations')
                        ->label('Export Locations')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            // Export location data to CSV or other format
                            // Implementation depends on your requirements
                        }),

                    Tables\Actions\BulkAction::make('clear_locations')
                        ->label('Clear Locations')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'location' => null,
                                    'home_location' => null,
                                    'office_location' => null,
                                ]);
                            });
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add relations here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),

            // You can add a view page for detailed location information
            // 'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Show count of users with locations
        $count = static::getModel()::whereNotNull('location')->count();
        return $count > 0 ? (string) $count : null;
    }
}
