<?php

namespace App\Filament\Resources\PluginDownloadResource\Pages;

use App\Filament\Resources\PluginDownloadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPluginDownload extends EditRecord
{
    protected static string $resource = PluginDownloadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
