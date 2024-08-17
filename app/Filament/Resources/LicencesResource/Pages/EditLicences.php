<?php

namespace App\Filament\Resources\LicencesResource\Pages;

use App\Filament\Resources\LicencesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLicences extends EditRecord
{
    protected static string $resource = LicencesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
