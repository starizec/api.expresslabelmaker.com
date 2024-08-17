<?php

namespace App\Filament\Resources\UsersResource\Pages;

use App\Filament\Resources\UsersResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class CreateUsers extends CreateRecord
{
    protected static string $resource = UsersResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return static::getModel()::create($data);
    }
}
