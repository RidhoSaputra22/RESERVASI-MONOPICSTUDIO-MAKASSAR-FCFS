<?php

namespace App\Filament\Resources\Photographers\Pages;

use App\Filament\Resources\Photographers\PhotographerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPhotographer extends EditRecord
{
    protected static string $resource = PhotographerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
