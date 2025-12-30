<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('package_id')
                    ->relationship('package', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('photographer_id')
                    ->relationship('photographer', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('studio_id')
                    ->relationship('studio', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                DateTimePicker::make('scheduled_at')
                    ->nullable(),
                Select::make('status')
                    ->options(BookingStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('snap_token')
                    ->nullable(),
                TextInput::make('code')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
