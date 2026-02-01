<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\GenericDatabaseNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengirim reminder kepada user 30 menit sebelum booking dimulai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memeriksa booking yang akan dimulai dalam 30 menit...');

        // Waktu sekarang + 30 menit
        $reminderTime = Carbon::now()->addMinutes(30);

        // Range waktu: 30-31 menit ke depan
        // Ini memberi buffer agar tidak terlewat jika scheduler berjalan sedikit telat
        $startTime = $reminderTime->copy()->subMinute();
        $endTime = $reminderTime->copy()->addMinute();

        // Ambil booking yang statusnya confirmed dan akan dimulai dalam 30 menit
        $bookings = Booking::with(['user', 'package', 'studio', 'photographer'])
            ->where('status', BookingStatus::Confirmed)
            ->whereBetween('scheduled_at', [$startTime, $endTime])
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('Tidak ada booking yang perlu diingatkan.');

            return 0;
        }

        $count = 0;
        foreach ($bookings as $booking) {
            try {
                $scheduledTime = $booking->scheduled_at->format('d M Y H:i');

                $message = "Reminder: Booking Anda akan segera dimulai!\n\n";
                $message .= "Kode Booking: {$booking->code}\n";
                $message .= "Paket: {$booking->package->name}\n";
                $message .= "Studio: {$booking->studio->name}\n";
                $message .= "Photographer: {$booking->photographer->name}\n";
                $message .= "Waktu: {$scheduledTime}\n\n";
                $message .= 'Booking Anda akan dimulai dalam 30 menit. Mohon bersiap dan tiba tepat waktu.';

                // Kirim notifikasi menggunakan GenericDatabaseNotification
                $booking->user->notify(
                    new GenericDatabaseNotification(
                        message: $message,
                        kind: 'booking_reminder',
                        extra: [
                            'booking_id' => $booking->id,
                            'booking_code' => $booking->code,
                            'scheduled_at' => $booking->scheduled_at->toIso8601String(),
                        ]
                    )
                );

                $count++;
                $this->info("✓ Reminder terkirim untuk booking {$booking->code} - User: {$booking->user->name}");

                Log::info('Booking reminder sent', [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->code,
                    'user_id' => $booking->user->id,
                    'scheduled_at' => $booking->scheduled_at,
                ]);
            } catch (\Exception $e) {
                $this->error("✗ Gagal mengirim reminder untuk booking {$booking->code}: {$e->getMessage()}");

                Log::error('Failed to send booking reminder', [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("\nSelesai! Total reminder terkirim: {$count} dari {$bookings->count()} booking.");

        return 0;
    }
}
