<?php

namespace App\Filament\Resources\Photographers\Pages;

use App\Filament\Resources\Photographers\PhotographerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePhotographer extends CreateRecord
{
    protected static string $resource = PhotographerResource::class;
}
