<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsersResource\Pages;
use App\Filament\Resources\UsersResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('first_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ]),

                Forms\Components\Section::make('Company Information')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_address')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('town')
                            ->maxLength(255),
                        Forms\Components\Select::make('country')
                            ->options([
                                'HR' => 'Hrvatska',
                                'SI' => 'Slovenija',
                                'BA' => 'Bosna i Hercegovina',
                                'RS' => 'Srbija',
                                'ME' => 'Crna Gora',
                                'MK' => 'Sjeverna Makedonija',
                            ]),
                        Forms\Components\TextInput::make('vat_number')
                            ->maxLength(50),
                    ]),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\Toggle::make('is_admin')
                            ->label('Admin Access')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'HR' => 'Hrvatska',
                        'SI' => 'Slovenija',
                        'BA' => 'Bosna i Hercegovina',
                        'RS' => 'Srbija',
                        'ME' => 'Crna Gora',
                        'MK' => 'Sjeverna Makedonija',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_admin')
                    ->boolean()
                    ->label('Admin'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d.m.Y')
                    ->label('Registered'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUsers::route('/create'),
            'edit' => Pages\EditUsers::route('/{record}/edit'),
        ];
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
}
