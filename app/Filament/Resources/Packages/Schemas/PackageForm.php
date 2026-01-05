<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('photo')
                    ->label('Foto Paket')
                    ->directory('packages')
                    ->disk('public')
                    ->visibility('public')
                    ->image()
                    ->columnSpanFull()
                    ->nullable(),
                TextInput::make('name')
                    ->label('Nama Paket')
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull()
                    ->nullable(),
                RichEditor::make('fasilitas')

                    ->json()
                    ->label('Fasilitas')
                    ->columnSpanFull()
                    ->nullable(),
                TextInput::make('price')
                    ->label('Harga')
                    ->prefix('Rp ')
                    ->numeric()
                    ->required(),
                TextInput::make('duration_minutes')
                    ->label('Durasi (Menit)')
                    ->numeric()
                    ->required(),
            ]);
    }
}
