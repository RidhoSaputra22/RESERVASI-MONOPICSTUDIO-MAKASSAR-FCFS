<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Photographer;
use App\Models\Studio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TestBookingReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:booking-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test fitur reminder 30 menit dengan user saputra22022@gmail.com';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Booking Reminder 30 Menit...');
        $this->newLine();

        // Cari user dengan email saputra22022@gmail.com
        $user = User::where('email', 'saputra22022@gmail.com')->first();

        if (!$user) {
            $this->error('❌ User dengan email saputra22022@gmail.com tidak ditemukan!');
            $this->info('Silakan buat user terlebih dahulu atau gunakan email yang ada di database.');
            return 1;
        }

        $this->info("✓ User ditemukan: {$user->name} ({$user->email})");

        // Ambil data package, photographer, dan studio pertama untuk testing
        $package = Package::first();
        $photographer = Photographer::first();
        $studio = Studio::first();

        if (!$package || !$photographer || !$studio) {
            $this->error('❌ Data package, photographer, atau studio tidak tersedia!');
            $this->info('Pastikan database sudah di-seed dengan data yang diperlukan.');
            return 1;
        }

        // Buat atau update booking yang scheduled_at-nya 30 menit dari sekarang
        $scheduledAt = Carbon::now()->addMinutes(30);

        $booking = Booking::updateOrCreate(
            [
                'user_id' => $user->id,
                'status' => BookingStatus::Confirmed,
            ],
            [
                'package_id' => $package->id,
                'photographer_id' => $photographer->id,
                'studio_id' => $studio->id,
                'scheduled_at' => $scheduledAt,
                'status' => BookingStatus::Confirmed,
            ]
        );

        $this->newLine();
        $this->info('✓ Booking berhasil dibuat/diupdate:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Kode Booking', $booking->code],
                ['User', $user->name],
                ['Package', $package->name],
                ['Studio', $studio->name],
                ['Photographer', $photographer->name],
                ['Scheduled At', $scheduledAt->format('d M Y H:i:s')],
                ['Status', $booking->status->value],
            ]
        );

        $this->newLine();
        $this->info('🔔 Menjalankan command bookings:send-reminders...');
        $this->newLine();

        // Jalankan command send reminders
        Artisan::call('bookings:send-reminders');
        $this->line(Artisan::output());

        $this->newLine();
        $this->info('✅ Testing selesai!');
        $this->info('💡 Cek notifikasi user untuk melihat reminder yang dikirim.');
        $this->info("💡 Link konfirmasi: " . route('booking.confirm-readiness', ['bookingId' => $booking->id]));

        return 0;
    }
}
