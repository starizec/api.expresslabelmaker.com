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
use Filament\Tables\Filters\Filter;

class ApiErrorsResource extends Resource
{
    protected static ?string $model = ApiError::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3) // 3 columns for the first row
                    ->schema([
                        Forms\Components\TextInput::make('created_at'),
                        Forms\Components\TextInput::make('user.email'),
                        Forms\Components\TextInput::make('error_status'),
                    ]),
                Forms\Components\Grid::make(1) // 1 column for the next set of fields
                    ->schema([
                        Forms\Components\Textarea::make('error_message')->autosize(),
                        Forms\Components\Textarea::make('stack_trace')->autosize(),
                        Forms\Components\Textarea::make('request')->autosize(),
                        Forms\Components\Textarea::make('log')->autosize(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->date('d.m.Y')->sortable(),
                Tables\Columns\TextColumn::make('user.email')->sortable(),
                Tables\Columns\TextColumn::make('error_status')->sortable(),
                Tables\Columns\TextColumn::make('error_message')->sortable(),
                Tables\Columns\TextColumn::make('stack_trace')->sortable(),
                Tables\Columns\TextColumn::make('request')->sortable(),
                Tables\Columns\TextColumn::make('log')->sortable()
            ])
            ->filters([
                Filter::make('user_email')
                    ->form([
                        Forms\Components\TextInput::make('email')->label('Search by Email'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['email'], function ($query, $email) {
                            $query->whereHas('user', function ($query) use ($email) {
                                $query->where('email', 'like', "%{$email}%");
                            });
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('id', 'desc');
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
