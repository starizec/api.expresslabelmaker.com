<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $mainData = [
            'cover_image' => $data['cover_image'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];

        $record->update($mainData);

        // Update English translation
        if (isset($data['title_en'])) {
            $record->translateOrNew('en')->title = $data['title_en'];
            $record->translateOrNew('en')->slug = $data['slug_en'];
            $record->translateOrNew('en')->content = $data['content_en'];
        }

        // Update Croatian translation
        if (isset($data['title_hr'])) {
            $record->translateOrNew('hr')->title = $data['title_hr'];
            $record->translateOrNew('hr')->slug = $data['slug_hr'];
            $record->translateOrNew('hr')->content = $data['content_hr'];
        }

        $record->save();

        return $record;
    }
}
