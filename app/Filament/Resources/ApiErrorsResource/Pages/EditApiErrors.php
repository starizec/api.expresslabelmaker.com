<?php

namespace App\Filament\Resources\ApiErrorsResource\Pages;

use App\Filament\Resources\ApiErrorsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApiErrors extends EditRecord
{
    protected static string $resource = ApiErrorsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
