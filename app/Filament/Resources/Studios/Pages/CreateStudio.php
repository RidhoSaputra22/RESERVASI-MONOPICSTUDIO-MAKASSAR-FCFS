<?php

namespace App\Filament\Resources\Studios\Pages;

use App\Filament\Resources\Studios\StudioResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStudio extends CreateRecord
{
    protected static string $resource = StudioResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['capacity'] = 100; // Set default capacity to 0

        return parent::handleRecordCreation($data);
    }
}
