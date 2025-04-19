<?php

namespace App\Filament\Resources\DeliveryLocationHeadersResource\Pages;

use App\Filament\Resources\DeliveryLocationHeadersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryLocationHeaders extends EditRecord
{
    protected static string $resource = DeliveryLocationHeadersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
