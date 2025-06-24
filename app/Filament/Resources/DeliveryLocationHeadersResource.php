<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryLocationHeadersResource\Pages;
use App\Filament\Resources\DeliveryLocationHeadersResource\RelationManagers;
use App\Models\DeliveryLocationHeader;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryLocationHeadersResource extends Resource
{
    protected static ?string $model = DeliveryLocationHeader::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Delivery Locations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('courier_id')
                    ->relationship('courier', 'name')
                    ->required(),
                Forms\Components\TextInput::make('location_count')
                    ->required()
                    ->integer(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('courier.name'),
                Tables\Columns\TextColumn::make('courier.country.short')
                    ->label('Country'),
                Tables\Columns\TextColumn::make('location_count'),
                Tables\Columns\TextColumn::make('geojson_file_name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('overseas')
                    ->url(fn() => route('overseas-delivery-locations'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-globe-alt')
                    ->label('Overseas Locations'),
                Tables\Actions\Action::make('dpd')
                    ->url(fn() => route('dpd-delivery-locations'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-globe-alt')
                    ->label('DPD Locations'),
                Tables\Actions\Action::make('hp')
                    ->url(fn() => route('hp-delivery-locations'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-globe-alt')
                    ->label('HP Locations'),
                Tables\Actions\Action::make('gls')
                    ->url(fn() => route('gls-delivery-locations'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-globe-alt')
                    ->label('GLS Locations'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryLocationHeaders::route('/'),
            'create' => Pages\CreateDeliveryLocationHeaders::route('/create'),
            'edit' => Pages\EditDeliveryLocationHeaders::route('/{record}/edit'),
        ];
    }
}
