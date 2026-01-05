<?php

namespace App\Services;

use App\Models\{
    Booking,
    Photographer,
    SesiFoto,
    Studio,
    Package,
    User,
    Holiday
};
use App\Enums\BookingStatus;
use App\Enums\NotificationType;
use App\Notifications\GenericDatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Carbon\Carbon;

use App\Jobs\SendWhatsAppBookingNotification;
use App\Services\WhatsAppServices;
use Illuminate\Support\Facades\Log;

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

    public static function getAvailableTimeSlots(
        ?string $date,
        int $durationMinutes,
        ?int $studioId = null,
        ?int $photographerId = null,
    ): array {
        if (empty($date)) {
            return [];
        }

        Carbon::setLocale('id');
        $tz = 'Asia/Makassar';

        $slotTimes = SesiFoto::orderBy('session_time')
            ->pluck('session_time')
            ->map(fn ($time) => Carbon::createFromTimeString((string) $time)->format('H:i'))
            ->toArray();

        if (empty($slotTimes)) {
            return [];
        }

        // Batas operasional harus pakai tanggal yang dipilih (bukan tanggal hari ini)
        $operationalStart = Carbon::parse("$date {$slotTimes[0]}", $tz);
        $operationalEnd = Carbon::parse("$date {$slotTimes[count($slotTimes) - 1]}", $tz)
            ->addMinutes($durationMinutes);

        $totalStudios = $studioId !== null
            ? 1
            : Studio::query()->count();

        $totalPhotographers = $photographerId !== null
            ? 1
            : Photographer::query()->where('is_available', true)->count();

        $takenBookingsQuery = Booking::query()
            ->whereDate('scheduled_at', $date)
            ->whereNotNull('scheduled_at')
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->when($studioId !== null, fn ($q) => $q->where('studio_id', $studioId))
            ->when($photographerId !== null, fn ($q) => $q->where('photographer_id', $photographerId))
            ->with('package');

        $takenIntervals = $takenBookingsQuery
            ->get()
            ->map(function (Booking $booking) use ($durationMinutes, $tz) {
                $start = $booking->scheduled_at
                    ?->copy()
                    ->setTimezone($tz);

                $bookingDuration = $booking->package?->duration_minutes;
                $end = $booking->scheduled_at
                    ?->copy()
                    ->setTimezone($tz)
                    ->addMinutes($bookingDuration ?? $durationMinutes);

                return [
                    'start' => $start,
                    'end' => $end,
                ];
            });

        return collect($slotTimes)
            ->map(function (string $time) use ($date, $durationMinutes, $operationalStart, $operationalEnd, $takenIntervals, $totalPhotographers, $totalStudios, $tz) {
                $slotStart = Carbon::parse("$date $time", $tz);
                $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

                if ($slotStart->lt($operationalStart) || $slotEnd->gt($operationalEnd)) {
                    return [
                        'time' => $time,
                        'available' => false,
                    ];
                }

                if ($totalPhotographers <= 0 || $totalStudios <= 0) {
                    return [
                        'time' => $time,
                        'available' => false,
                    ];
                }

                $overlapCount = $takenIntervals->filter(function (array $taken) use ($slotStart, $slotEnd) {
                    $takenStart = $taken['start'];
                    $takenEnd = $taken['end'];

                    if (! $takenStart || ! $takenEnd) {
                        return false;
                    }

                    return $slotStart->lt($takenEnd) && $slotEnd->gt($takenStart);
                })->count();

                $available = $overlapCount < $totalPhotographers && $overlapCount < $totalStudios;

                return [
                    'time' => $time,
                    'available' => $available,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Membuat reservasi baru dan inisialisasi pembayaran
     */
    public function createReservation(array $data)
    {
        return DB::transaction(function () use ($data) {
            $name = (string) ($data['name'] ?? 'Customer');
            $email = $data['email'] ?? null;
            $phone = (string) ($data['phone'] ?? '');

            $userId = $data['user_id'] ?? null;
            $user = null;

            if (is_numeric($userId)) {
                $user = User::query()->find((int) $userId);
            }

            if (! $user) {
                $authUser = Auth::user();
                $user = $authUser instanceof User ? $authUser : null;
            }

            if (! $user) {
                $user = $this->resolveOrCreateUser(
                    name: $name,
                    email: is_string($email) ? $email : null,
                    phone: $phone,
                );
            }

            // Ambil paket
            $package = Package::findOrFail($data['package_id']);

            $scheduledAt = $data['scheduled_at'] ?? null;
            if (is_string($scheduledAt) && $scheduledAt !== '') {
                $scheduledAt = Carbon::parse($scheduledAt, 'Asia/Makassar');
            }

            // Buat booking (masih pending)
            $booking = Booking::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'scheduled_at' => $scheduledAt instanceof Carbon ? $scheduledAt : null,
                'status' => BookingStatus::Pending,
            ]);



            // Buat parameter pembayaran Midtrans
            $params = [
                'transaction_details' => [
                    // Gunakan code booking agar callback bisa mencari booking tanpa kolom order_id khusus.
                    'order_id' => $booking->code,
                    'gross_amount' => $package->price,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->hp,
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

            $user->notify(new GenericDatabaseNotification(
                message: 'Booking berhasil dibuat. Silakan lakukan pembayaran untuk konfirmasi.',
                kind: NotificationType::BookingCreated->value,
                extra: [
                    'booking_id' => $booking->id,
                    'code' => $booking->code,
                ],
            ));

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
        $result = $this->processPaymentResult($payload);

        return response()->json(
            ['message' => $result['message']],
            $result['status'],
        );
    }

    /**
     * Proses hasil pembayaran (bisa dipanggil dari Livewire atau callback HTTP)
     *
     * @return array{ok: bool, status: int, message: string, booking: ?Booking}
     */
    public function processPaymentResult(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        if (! is_string($orderId) || $orderId === '') {
            return [
                'ok' => false,
                'status' => 400,
                'message' => 'Invalid order_id',
                'booking' => null,
            ];
        }

        $booking = Booking::query()->where('code', $orderId)->first();

        if (! $booking) {
            return [
                'ok' => false,
                'status' => 404,
                'message' => 'Booking not found',
                'booking' => null,
            ];
        }

        if (in_array($transactionStatus, ['capture', 'settlement'], true)) {
            $booking->update(['status' => BookingStatus::Confirmed]);
            $this->processFCFS($booking);

            return [
                'ok' => true,
                'status' => 200,
                'message' => 'Payment confirmed',
                'booking' => $booking,
            ];
        }

        if (in_array($transactionStatus, ['cancel', 'expire', 'deny'], true)) {
            $booking->update(['status' => BookingStatus::Cancelled]);

            $booking->loadMissing('user');
            $booking->user?->notify(new GenericDatabaseNotification(
                message: 'Pembayaran dibatalkan / kedaluwarsa. Booking Anda dibatalkan.',
                kind: NotificationType::Cancelled->value,
                extra: ['booking_id' => $booking->id, 'code' => $booking->code],
            ));

            return [
                'ok' => false,
                'status' => 200,
                'message' => 'Payment cancelled',
                'booking' => $booking,
            ];
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Payment pending',
            'booking' => $booking,
        ];
    }

    /**
     * Algoritma FCFS → Menentukan fotografer & studio pertama yang tersedia
     */
    protected function processFCFS(Booking $booking)
    {
        $booking->loadMissing(['package', 'user']);

        $tz = 'Asia/Makassar';
        $durationMinutes = (int) ($booking->package?->duration_minutes ?? 60);

        $scheduledAt = $booking->scheduled_at
            ? $booking->scheduled_at->copy()->setTimezone($tz)
            : null;

        $assignment = $scheduledAt
            ? $this->findAvailableAssignment($scheduledAt, $durationMinutes, $booking->id)
            : null;

        if (! $assignment) {
            $assignment = $this->findNextAvailableAssignment(
                startDate: Carbon::now($tz)->addDay()->startOfDay(),
                durationMinutes: $durationMinutes,
                excludeBookingId: $booking->id,
                maxDays: 30,
            );
        }

        if (! $assignment) {
            $booking->user?->notify(new GenericDatabaseNotification(
                message: 'Pembayaran berhasil, namun saat ini semua jadwal penuh. Tim kami akan menghubungi Anda untuk penjadwalan ulang.',
                kind: NotificationType::BookingConfirmed->value,
                extra: ['booking_id' => $booking->id, 'code' => $booking->code],
            ));
            return;
        }

        $booking->update([
            'photographer_id' => $assignment['photographer_id'],
            'studio_id' => $assignment['studio_id'],
            'scheduled_at' => $assignment['scheduled_at'],
            'status' => BookingStatus::Confirmed,
        ]);

        $formatted = Carbon::parse($assignment['scheduled_at'], $tz)->format('d-m-Y H:i');

        $booking->user?->notify(new GenericDatabaseNotification(
            message: "Booking Anda dikonfirmasi. Jadwal sesi foto: {$formatted}.",
            kind: NotificationType::BookingConfirmed->value,
            extra: ['booking_id' => $booking->id, 'code' => $booking->code],
        ));

        $photographer = Photographer::find($assignment['photographer_id']);
        $photographer?->notify(new GenericDatabaseNotification(
            message: "Anda ditugaskan untuk sesi foto {$booking->code} pada {$formatted}.",
            kind: NotificationType::BookingConfirmed->value,
            extra: ['booking_id' => $booking->id, 'code' => $booking->code],
        ));

        // Send WhatsApp notification (queued)
        SendWhatsAppBookingNotification::dispatch($booking->id);
    }

    private function resolveOrCreateUser(string $name, ?string $email, string $phone): User
    {
        $hp = trim($phone);
        $hpDigits = preg_replace('/\D+/', '', $hp) ?: (string) Str::random(10);

        $resolvedEmail = $email;
        if (! is_string($resolvedEmail) || $resolvedEmail === '') {
            $resolvedEmail = 'guest+' . $hpDigits . '@example.test';
        }

        $existing = User::query()
            ->where('email', $resolvedEmail)
            ->orWhere('hp', $hp)
            ->first();

        if ($existing) {
            return $existing;
        }

        $uniqueEmail = $resolvedEmail;
        $counter = 2;
        while (User::query()->where('email', $uniqueEmail)->exists()) {
            $uniqueEmail = 'guest+' . $hpDigits . '+' . $counter . '@example.test';
            $counter++;
        }

        $uniqueHp = $hp !== '' ? $hp : $hpDigits;
        $hpCounter = 2;
        while (User::query()->where('hp', $uniqueHp)->exists()) {
            $uniqueHp = $hpDigits . '-' . $hpCounter;
            $hpCounter++;
        }

        return User::query()->create([
            'name' => $name,
            'email' => $uniqueEmail,
            'hp' => $uniqueHp,
            'password' => bcrypt(Str::random(32)),
            // role default sudah ada di migration
        ]);
    }

    private function findNextAvailableAssignment(Carbon $startDate, int $durationMinutes, int $excludeBookingId, int $maxDays = 30): ?array
    {
        $tz = 'Asia/Makassar';

        for ($i = 0; $i <= $maxDays; $i++) {
            $date = $startDate->copy()->addDays($i);

            if (Holiday::query()->whereDate('date', $date->toDateString())->exists()) {
                continue;
            }

            $slots = self::getAvailableTimeSlots(
                date: $date->toDateString(),
                durationMinutes: $durationMinutes,
            );

            foreach ($slots as $slot) {
                if (! ($slot['available'] ?? false)) {
                    continue;
                }

                $scheduledAt = Carbon::parse($date->toDateString() . ' ' . $slot['time'], $tz);
                $assignment = $this->findAvailableAssignment($scheduledAt, $durationMinutes, $excludeBookingId);
                if ($assignment) {
                    return $assignment;
                }
            }
        }

        return null;
    }

    private function findAvailableAssignment(Carbon $scheduledAt, int $durationMinutes, int $excludeBookingId): ?array
    {
        $tz = 'Asia/Makassar';
        $start = $scheduledAt->copy()->setTimezone($tz);
        $end = $start->copy()->addMinutes($durationMinutes);

        $date = $start->toDateString();

        $busyBookings = Booking::query()
            ->whereDate('scheduled_at', $date)
            ->whereNotNull('scheduled_at')
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->where('id', '!=', $excludeBookingId)
            ->with('package')
            ->get();

        $busyPhotographerIds = [];
        $busyStudioIds = [];

        foreach ($busyBookings as $b) {
            $bStart = $b->scheduled_at?->copy()->setTimezone($tz);
            if (! $bStart) {
                continue;
            }

            $bDuration = (int) ($b->package?->duration_minutes ?? $durationMinutes);
            $bEnd = $bStart->copy()->addMinutes($bDuration);

            $overlap = $start->lt($bEnd) && $end->gt($bStart);
            if (! $overlap) {
                continue;
            }

            if ($b->photographer_id) {
                $busyPhotographerIds[] = (int) $b->photographer_id;
            }
            if ($b->studio_id) {
                $busyStudioIds[] = (int) $b->studio_id;
            }
        }

        $busyPhotographerIds = array_values(array_unique($busyPhotographerIds));
        $busyStudioIds = array_values(array_unique($busyStudioIds));

        $photographer = Photographer::query()
            ->where('is_available', true)
            ->when($busyPhotographerIds !== [], fn ($q) => $q->whereNotIn('id', $busyPhotographerIds))
            ->first();

        $studio = Studio::query()
            ->when($busyStudioIds !== [], fn ($q) => $q->whereNotIn('id', $busyStudioIds))
            ->first();

        if (! $photographer || ! $studio) {
            return null;
        }

        return [
            'scheduled_at' => $start,
            'photographer_id' => $photographer->id,
            'studio_id' => $studio->id,
        ];
    }
}
