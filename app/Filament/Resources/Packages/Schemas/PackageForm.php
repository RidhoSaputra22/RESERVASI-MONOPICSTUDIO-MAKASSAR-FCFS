<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->nullable(),
                TextInput::make('price')
                    ->numeric()
                    ->required(),
                TextInput::make('duration_minutes')
                    ->numeric()
                    ->required(),
            ]);
    }
}
