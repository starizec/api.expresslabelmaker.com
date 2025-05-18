<?php

namespace App\Filament\Resources\PluginDownloadResource\Pages;

use App\Filament\Resources\PluginDownloadResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreatePluginDownload extends CreateRecord
{
    protected static string $resource = PluginDownloadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['plugin_download_link']) && isset($data['version'])) {
            $originalPath = $data['plugin_download_link']; // npr. storage/app/livewire-tmp/file.zip
            $originalFullPath = storage_path('app/' . $originalPath);

            $originalName = basename($originalFullPath); // npr. file.zip
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);

            $newName = 'express-label-maker-' . $data['version'] . '.' . $extension;
            $newRelativePath = 'plugin-downloads/' . $newName;
            $newFullPath = storage_path('app/' . $newRelativePath);

            // Premještanje datoteke
            if (file_exists($originalFullPath)) {
                rename($originalFullPath, $newFullPath);
            }

            // Ažuriraj podatak u bazi s relativnom putanjom (bez 'storage/app')
            $data['plugin_download_link'] = $newRelativePath;
        }

        return $data;
    }

}
