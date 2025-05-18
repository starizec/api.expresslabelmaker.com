<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PluginDownloadResource\Pages;
use App\Filament\Resources\PluginDownloadResource\RelationManagers;
use App\Models\PluginDownload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PluginDownloadResource extends Resource
{
    protected static ?string $model = PluginDownload::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('version')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('download_link')
                    ->required()
                    ->directory('plugin-downloads')
                    ->preserveFilenames()
                    ->storeFileNamesIn('original_filename')
                    ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                        $version = $get('version');
                        if ($version && $state) {
                            $extension = pathinfo($state, PATHINFO_EXTENSION);
                            $newFilename = str_replace('.', '_', $version) . '.' . $extension;
                            $set('download_link', $newFilename);
                        }
                    })->multiple(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('download_link')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPluginDownloads::route('/'),
            'create' => Pages\CreatePluginDownload::route('/create'),
            'edit' => Pages\EditPluginDownload::route('/{record}/edit'),
        ];
    }
}
