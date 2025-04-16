<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryLocationsResource\Pages;
use App\Models\DeliveryLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveryLocationsResource extends Resource
{
    protected static ?string $model = DeliveryLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Delivery Locations';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('courier')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('location_id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('place')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('street')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->options([
                        'HR' => 'Croatia',
                        'SI' => 'Slovenia',
                    ]),
                Tables\Filters\SelectFilter::make('courier')
                    ->options([
                        'DPD' => 'DPD',
                        'HP' => 'HP',
                        'OVERSEAS' => 'Overseas',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'parcel_locker' => 'Parcel Locker',
                        'pickup_point' => 'Pickup Point',
                    ]),
                Tables\Filters\SelectFilter::make('active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('overseas')
                    ->url(fn () => route('delivery-locations'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-globe-alt')
                    ->label('Overseas Locations'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryLocations::route('/'),
        ];
    }
}
