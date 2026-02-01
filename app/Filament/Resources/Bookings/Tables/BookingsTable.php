<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Booking')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('package.name')
                    ->label('Paket')
                    ->searchable(),
                TextColumn::make('photographer.name')
                    ->label('Fotografer')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('studio.name')
                    ->label('Studio')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('scheduled_at')
                    ->label('Jadwal Pemotretan')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('readiness_confirmed_at')
                    ->label('Konfirmasi Kesiapan')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->description(fn ($record) => $record->readiness_confirmed_at
                        ? 'User sudah konfirmasi siap'
                        : 'Belum dikonfirmasi')
                    ->color(fn ($record) => $record->readiness_confirmed_at ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
