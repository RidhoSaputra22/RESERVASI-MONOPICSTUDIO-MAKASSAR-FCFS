<?php

namespace App\Services;

use App\Models\{
    Booking,
    Customer,
    Photographer,
    Studio,
    Package,
    Notification
};
use App\Enums\BookingStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Carbon\Carbon;

class ReservationService
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        MidtransConfig::$serverKey = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    /**
     * Membuat reservasi baru dan inisialisasi pembayaran
     */
    public function createReservation(array $data)
    {
        return DB::transaction(function () use ($data) {

            // Pastikan customer terdaftar
            $customer = Customer::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'phone' => $data['phone']]
            );

            // Ambil paket
            $package = Package::findOrFail($data['package_id']);

            // Buat booking (masih pending)
            $booking = Booking::create([
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'status' => BookingStatus::Pending,
            ]);

            // Buat parameter pembayaran Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => 'BOOK-' . strtoupper(Str::random(8)),
                    'gross_amount' => $package->price,
                ],
                'customer_details' => [
                    'first_name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
                'item_details' => [
                    [
                        'id' => $package->id,
                        'price' => $package->price,
                        'quantity' => 1,
                        'name' => $package->name,
                    ],
                ],
            ];

            // Dapatkan Snap Token
            $snapToken = Snap::getSnapToken($params);

            // Simpan Snap Token ke booking
            $booking->update(['snap_token' => $snapToken]);

            return [
                'booking' => $booking,
                'snap_token' => $snapToken,
            ];
        });
    }

    /**
     * Callback Midtrans → update booking status & jalankan FCFS
     */
    public function handlePaymentCallback(array $payload)
    {
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        $booking = Booking::where('snap_token', $orderId)->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if (in_array($transactionStatus, ['capture', 'settlement'])) {
            // Tandai pembayaran berhasil
            $booking->update(['status' => BookingStatus::Confirmed]);

            // Jalankan algoritma FCFS untuk menjadwalkan
            $this->processFCFS($booking);

            // Kirim notifikasi ke customer
            Notification::create([
                'notifiable_type' => Customer::class,
                'notifiable_id' => $booking->customer_id,
                'type' => 'booking_confirmed',
                'message' => "Pembayaran berhasil! Jadwal Anda sedang diproses.",
            ]);
        } elseif (in_array($transactionStatus, ['cancel', 'expire', 'deny'])) {
            $booking->update(['status' => BookingStatus::Cancelled]);
        }

        return response()->json(['message' => 'Callback processed']);
    }

    /**
     * Algoritma FCFS → Menentukan fotografer & studio pertama yang tersedia
     */
    protected function processFCFS(Booking $booking)
    {
        $availablePhotographer = Photographer::where('is_available', true)->first();
        $availableStudio = Studio::first();
        $holiday = \App\Models\Holiday::whereDate('date', Carbon::today())->exists();

        if ($holiday || !$availablePhotographer || !$availableStudio) {
            // Jika tidak ada slot, biarkan tetap confirmed tanpa jadwal
            return;
        }

        $scheduledAt = Carbon::now()->addDay()->setTime(10, 0); // jadwal otomatis besok jam 10 pagi

        $booking->update([
            'photographer_id' => $availablePhotographer->id,
            'studio_id' => $availableStudio->id,
            'scheduled_at' => $scheduledAt,
            'status' => BookingStatus::Confirmed,
        ]);

        // Tandai fotografer jadi sibuk
        $availablePhotographer->update(['is_available' => false]);

        // Notifikasi ke customer
        Notification::create([
            'notifiable_type' => Customer::class,
            'notifiable_id' => $booking->customer_id,
            'type' => 'booking_scheduled',
            'message' => "Sesi foto Anda dijadwalkan pada {$scheduledAt->format('d-m-Y H:i')}.",
        ]);

        // Notifikasi ke fotografer
        Notification::create([
            'notifiable_type' => Photographer::class,
            'notifiable_id' => $availablePhotographer->id,
            'type' => 'assignment',
            'message' => "Anda ditugaskan untuk sesi foto {$booking->code} pada {$scheduledAt->format('d-m-Y H:i')}.",
        ]);
    }
}