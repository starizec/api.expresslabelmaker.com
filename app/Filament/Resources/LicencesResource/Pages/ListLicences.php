<?php

namespace App\Filament\Resources\LicencesResource\Pages;

use App\Filament\Resources\LicencesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLicences extends ListRecords
{
    protected static string $resource = LicencesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
