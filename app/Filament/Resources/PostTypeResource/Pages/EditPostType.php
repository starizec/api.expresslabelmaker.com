<?php

namespace App\Filament\Resources\PostTypeResource\Pages;

use App\Filament\Resources\PostTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPostType extends EditRecord
{
    protected static string $resource = PostTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
