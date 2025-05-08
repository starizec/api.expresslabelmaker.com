<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('post_type_id')
                    ->relationship('postType', 'name')
                    ->required(),
                Forms\Components\FileUpload::make('cover_image')
                    ->image()
                    ->directory('posts')
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->default('draft'),
                Forms\Components\Tabs::make('Translations')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Croatian')
                            ->schema([
                                Forms\Components\TextInput::make('title_hr')
                                    ->required()
                                    ->maxLength(255)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $translation = $record->translations->where('locale', 'hr')->first();
                                            $component->state($translation?->title ?? '');
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug_hr')
                                    ->required()
                                    ->maxLength(255)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $translation = $record->translations->where('locale', 'hr')->first();
                                            $component->state($translation?->slug ?? '');
                                        }
                                    }),
                                Forms\Components\RichEditor::make('content_hr')
                                    ->required()
                                    ->columnSpanFull()
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $translation = $record->translations->where('locale', 'hr')->first();
                                            $component->state($translation?->content ?? '');
                                        }
                                    }),
                            ]),
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\TextInput::make('title_en')
                                    ->required()
                                    ->maxLength(255)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $translation = $record->translations->where('locale', 'en')->first();
                                            $component->state($translation?->title ?? '');
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug_en')
                                    ->required()
                                    ->maxLength(255)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $translation = $record->translations->where('locale', 'en')->first();
                                            $component->state($translation?->slug ?? '');
                                        }
                                    }),
                                Forms\Components\RichEditor::make('content_en')
                                    ->required()
                                    ->columnSpanFull()
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $translation = $record->translations->where('locale', 'en')->first();
                                            $component->state($translation?->content ?? '');
                                        }
                                    }),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->afterStateHydrated(function ($component, $state, $record) {
                        if ($record) {
                            // Ensure translations exist
                            $record->translateOrNew('en');
                            $record->translateOrNew('hr');
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
