<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\BookingStatus;
use App\Enums\NotificationType;
use App\Filament\Resources\Bookings\BookingResource;
use App\Notifications\GenericDatabaseNotification;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // notif user dan photographer ketika booking di batalkan
        $user = $record->user;
        $photographer = $record->photographer;

        $status = $data['status'] ?? null;

        switch ($status) {
            case NotificationType::Cancelled:
                $user->notify(new GenericDatabaseNotification(
                    message: 'Booking Anda dengan kode '.$record->code.' telah dibatalkan oleh Admin. silahkan hubungi kami jika ada pertanyaan.',
                    kind: NotificationType::Cancelled->value,
                ));

                $photographer->notify(new GenericDatabaseNotification(
                    message: 'Booking dengan kode '.$record->code.' telah dibatalkan oleh Admin. silahkan hubungi kami jika ada pertanyaan.',
                    kind: NotificationType::Cancelled->value,
                ));
                break;
        }

        return parent::handleRecordUpdate($record, $data);
    }
}
