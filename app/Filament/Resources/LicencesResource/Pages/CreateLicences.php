<?php

namespace App\Filament\Resources\LicencesResource\Pages;

use App\Filament\Resources\LicencesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use App\Models\Domain;

class CreateLicences extends CreateRecord
{
    protected static string $resource = LicencesResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        if ($data['licence_type_id'] == 1) {
            $data['usage_limit'] = config('usage.trial');
        } elseif ($data['licence_type_id'] == 2) {
            $data['usage_limit'] = config('usage.full');
        }

        $data['user_id'] = Domain::where('id', $data['domain_id'])->pluck('user_id')[0];
        $data['licence_uid'] = Uuid::uuid4()->toString();

        return static::getModel()::create($data);
    }
}
