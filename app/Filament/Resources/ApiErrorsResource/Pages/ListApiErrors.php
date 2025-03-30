<?php

namespace App\Filament\Resources\ApiErrorsResource\Pages;

use App\Filament\Resources\ApiErrorsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApiErrors extends ListRecords
{
    protected static string $resource = ApiErrorsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ApiErrorsResource\Widgets\ApiErrorsChart::class,
        ];
    }
}
