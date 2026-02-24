<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\BookingStatus;
use App\Notifications\GenericDatabaseNotification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
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
                DeleteAction::make()
                    ->before(function ($record) {
                        $record->loadMissing('user');

                        if (
                            $record->user &&
                            ! in_array($record->status, [BookingStatus::Cancelled, BookingStatus::Completed], true)
                        ) {
                            $record->user->notify(new GenericDatabaseNotification(
                                message: "Booking Anda dengan kode *{$record->code}* telah dibatalkan oleh admin.",
                                kind: 'booking_cancelled_by_admin',
                                extra: ['booking_id' => $record->id, 'code' => $record->code],
                            ));
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                $record->loadMissing('user');

                                if (
                                    $record->user &&
                                    ! in_array($record->status, [BookingStatus::Cancelled, BookingStatus::Completed], true)
                                ) {
                                    $record->user->notify(new GenericDatabaseNotification(
                                        message: "Booking Anda dengan kode *{$record->code}* telah dibatalkan oleh admin.",
                                        kind: 'booking_cancelled_by_admin',
                                        extra: ['booking_id' => $record->id, 'code' => $record->code],
                                    ));
                                }
                            }
                        }),
                ]),
            ]);
    }
}
