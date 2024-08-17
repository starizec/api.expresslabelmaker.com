<?php

namespace App\Filament\Resources\CouriersResource\Pages;

use App\Filament\Resources\CouriersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCouriers extends EditRecord
{
    protected static string $resource = CouriersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
