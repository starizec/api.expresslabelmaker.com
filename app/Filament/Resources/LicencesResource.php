<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicencesResource\Pages;
use App\Filament\Resources\LicencesResource\RelationManagers;
use App\Models\Licence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;


class LicencesResource extends Resource
{
    protected static ?string $model = Licence::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-rupee';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('domain_id')
                    ->relationship(
                        name: 'domain',
                        titleAttribute: 'name',
                    )->searchable(),
                Forms\Components\Select::make('licence_type_id')
                    ->options([
                        '1' => 'trial',
                        '2' => 'full',
                        '3' => 'admin',
                    ]),
                Forms\Components\DatePicker::make('valid_from')->default(now()),
                Forms\Components\DatePicker::make('valid_until')->default(now()->addYear())
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain.name')->sortable(),
                Tables\Columns\TextColumn::make('user.email')->sortable(),
                Tables\Columns\TextColumn::make('usage')->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')->sortable(),
                Tables\Columns\TextColumn::make('licence_uid'),
                Tables\Columns\TextColumn::make('valid_from')->date('d.m.Y')->sortable(),
                Tables\Columns\TextColumn::make('valid_until')->date('d.m.Y')->sortable()
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
                Filter::make('domain_name')
                    ->form([
                        Forms\Components\TextInput::make('name')->label('Search by Domain Name'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['name'], function ($query, $name) {
                            $query->whereHas('domain', function ($query) use ($name) {
                                $query->where('name', 'like', "%{$name}%");
                            });
                        });
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('id', 'desc');;
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
            'index' => Pages\ListLicences::route('/'),
            'create' => Pages\CreateLicences::route('/create'),
            'edit' => Pages\EditLicences::route('/{record}/edit'),
        ];
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }
}
