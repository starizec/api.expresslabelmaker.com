<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $mainData = [
            'user_id' => auth()->id(),
            'cover_image' => $data['cover_image'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ];

        $page = static::getModel()::create($mainData);

        // Set English translation
        if (isset($data['title_en'])) {
            $page->translateOrNew('en')->title = $data['title_en'];
            $page->translateOrNew('en')->slug = $data['slug_en'];
            $page->translateOrNew('en')->content = $data['content_en'];
        }

        // Set Croatian translation
        if (isset($data['title_hr'])) {
            $page->translateOrNew('hr')->title = $data['title_hr'];
            $page->translateOrNew('hr')->slug = $data['slug_hr'];
            $page->translateOrNew('hr')->content = $data['content_hr'];
        }

        $page->save();

        return $page;
    }
}
