<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiErrorsResource\Pages;
use App\Filament\Resources\ApiErrorsResource\RelationManagers;
use App\Models\ApiError;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApiErrorsResource extends Resource
{
    protected static ?string $model = ApiError::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListApiErrors::route('/'),
            'create' => Pages\CreateApiErrors::route('/create'),
            'edit' => Pages\EditApiErrors::route('/{record}/edit'),
        ];
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }
}
