<?php

namespace App\Filament\Resources\PluginDownloadResource\Pages;

use App\Filament\Resources\PluginDownloadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPluginDownloads extends ListRecords
{
    protected static string $resource = PluginDownloadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
