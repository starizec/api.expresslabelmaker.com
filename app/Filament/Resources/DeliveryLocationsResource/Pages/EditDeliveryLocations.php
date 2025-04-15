<?php

namespace App\Filament\Resources\DeliveryLocationsResource\Pages;

use App\Filament\Resources\DeliveryLocationsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryLocations extends EditRecord
{
    protected static string $resource = DeliveryLocationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
