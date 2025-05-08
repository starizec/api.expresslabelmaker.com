<?php

namespace App\Filament\Resources\PostTypeResource\Pages;

use App\Filament\Resources\PostTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostTypes extends ListRecords
{
    protected static string $resource = PostTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
