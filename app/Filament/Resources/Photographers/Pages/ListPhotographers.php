<?php

namespace App\Filament\Resources\Photographers\Pages;

use App\Filament\Resources\Photographers\PhotographerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPhotographers extends ListRecords
{
    protected static string $resource = PhotographerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
