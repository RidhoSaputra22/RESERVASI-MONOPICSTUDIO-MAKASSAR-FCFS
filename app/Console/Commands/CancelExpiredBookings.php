<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\GenericDatabaseNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membatalkan booking berstatus pending yang waktu jadwalnya sudah terlewati';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tz = 'Asia/Makassar';
        $now = Carbon::now($tz);

        $this->info("Memeriksa booking pending yang sudah terlewat (sekarang: {$now->format('d-m-Y H:i')})...");

        // Ambil semua booking yang masih pending dan scheduled_at-nya sudah lewat
        $expiredBookings = Booking::query()
            ->with(['user', 'package'])
            ->where('status', BookingStatus::Pending->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', $now)
            ->get();

        if ($expiredBookings->isEmpty()) {
            $this->info('Tidak ada booking yang perlu dibatalkan.');

            return 0;
        }

        $count = 0;

        foreach ($expiredBookings as $booking) {
            try {
                $booking->update(['status' => BookingStatus::Cancelled]);

                $formatted = $booking->scheduled_at
                    ->copy()
                    ->setTimezone($tz)
                    ->format('d-m-Y H:i');

                $booking->user?->notify(new GenericDatabaseNotification(
                    message: "Booking Anda ({$booking->code}) untuk jadwal {$formatted} telah otomatis dibatalkan karena belum dikonfirmasi (pembayaran tidak diselesaikan) dan waktu sesi sudah terlewati.",
                    kind: NotificationType::Cancelled->value,
                    extra: [
                        'booking_id' => $booking->id,
                        'code'       => $booking->code,
                        'reason'     => 'expired_pending',
                    ],
                ));

                $this->line("  ✓ Booking {$booking->code} dibatalkan (jadwal: {$formatted}).");
                Log::info("Booking {$booking->code} otomatis dibatalkan: pending melewati waktu jadwal ({$formatted}).");

                $count++;
            } catch (\Throwable $e) {
                $this->error("  ✗ Gagal membatalkan booking {$booking->code}: {$e->getMessage()}");
                Log::error("Gagal membatalkan booking {$booking->code}: {$e->getMessage()}");
            }
        }

        $this->info("Selesai. Total {$count} booking dibatalkan.");

        return 0;
    }
}
