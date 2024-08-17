<?php

namespace App\Filament\Resources\DomainsResource\Pages;

use App\Filament\Resources\DomainsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDomains extends ListRecords
{
    protected static string $resource = DomainsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
