<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\NotificationType;
use App\Jobs\SendWhatsAppBookingNotification;
use App\Models\Booking;
use App\Models\Holiday;
use App\Models\Package;
use App\Models\Photographer;
use App\Models\SesiFoto;
use App\Models\Studio;
use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

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
        ?int $excludeBookingId = null,
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
            ->when($excludeBookingId !== null, fn ($q) => $q->where('id', '!=', $excludeBookingId))
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

        $now = Carbon::now($tz);

        return collect($slotTimes)
            ->map(function (string $time) use ($date, $durationMinutes, $operationalStart, $operationalEnd, $takenIntervals, $totalPhotographers, $totalStudios, $tz, $now) {
                $slotStart = Carbon::parse("$date $time", $tz);
                $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

                // Tandai slot yang sudah terlewat sebagai tidak tersedia (hanya untuk hari ini)
                if ($date === $now->toDateString() && $slotStart->lte($now)) {
                    return [
                        'time' => $time,
                        'available' => false,
                    ];
                }

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
     * Batalkan booking milik user tertentu.
     *
     * @return array{ok: bool, status: int, message: string, booking: ?Booking}
     */
    public function cancelBookingByUser(int $bookingId, int $userId, ?string $reason = null): array
    {
        $booking = Booking::query()->with(['user', 'photographer', 'studio'])->find($bookingId);

        if (! $booking) {
            return ['ok' => false, 'status' => 404, 'message' => 'Booking not found', 'booking' => null];
        }

        if ((int) $booking->user_id !== (int) $userId) {
            return ['ok' => false, 'status' => 403, 'message' => 'Forbidden', 'booking' => null];
        }

        if ($booking->status === BookingStatus::Cancelled) {
            return ['ok' => true, 'status' => 200, 'message' => 'Booking already cancelled', 'booking' => $booking];
        }

        if ($booking->status === BookingStatus::Completed) {
            return ['ok' => false, 'status' => 422, 'message' => 'Booking sudah selesai dan tidak bisa dibatalkan', 'booking' => $booking];
        }

        return DB::transaction(function () use ($booking, $reason) {
            $booking->update(['status' => BookingStatus::Cancelled]);

            $extra = [
                'booking_id' => $booking->id,
                'code' => $booking->code,
            ];
            if (is_string($reason) && $reason !== '') {
                $extra['reason'] = $reason;
            }

            $booking->user?->notify(new GenericDatabaseNotification(
                message: 'Booking Anda berhasil dibatalkan.'.(is_string($reason) && $reason !== '' ? " Alasan: {$reason}." : ''),
                kind: NotificationType::Cancelled->value,
                extra: $extra,
            ));

            if ($booking->photographer) {
                $booking->photographer->notify(new GenericDatabaseNotification(
                    message: "Booking {$booking->code} dibatalkan oleh customer.",
                    kind: NotificationType::Cancelled->value,
                    extra: $extra,
                ));
            }

            return ['ok' => true, 'status' => 200, 'message' => 'Booking cancelled', 'booking' => $booking];
        });
    }

    /**
     * Jadwal ulang booking milik user tertentu.
     *
     * @return array{ok: bool, status: int, message: string, booking: ?Booking}
     */
    public function rescheduleBookingByUser(int $bookingId, int $userId, string $newScheduledAt): array
    {
        $tz = 'Asia/Makassar';

        $booking = Booking::query()->with(['package', 'user', 'photographer', 'studio'])->find($bookingId);

        if (! $booking) {
            return ['ok' => false, 'status' => 404, 'message' => 'Booking not found', 'booking' => null];
        }

        if ((int) $booking->user_id !== (int) $userId) {
            return ['ok' => false, 'status' => 403, 'message' => 'Forbidden', 'booking' => null];
        }

        if ($booking->status === BookingStatus::Cancelled) {
            return ['ok' => false, 'status' => 422, 'message' => 'Booking sudah dibatalkan', 'booking' => $booking];
        }

        if ($booking->status === BookingStatus::Completed) {
            return ['ok' => false, 'status' => 422, 'message' => 'Booking sudah selesai dan tidak bisa dijadwal ulang', 'booking' => $booking];
        }

        try {
            $target = Carbon::parse($newScheduledAt, $tz);
        } catch (\Throwable) {
            return ['ok' => false, 'status' => 422, 'message' => 'Format jadwal tidak valid', 'booking' => $booking];
        }

        if (Holiday::query()->whereDate('date', $target->toDateString())->exists()) {
            return ['ok' => false, 'status' => 422, 'message' => 'Tanggal yang dipilih libur', 'booking' => $booking];
        }

        $durationMinutes = (int) ($booking->package?->duration_minutes ?? 60);

        // Validasi jam harus termasuk sesi foto yang tersedia
        $slotTimes = SesiFoto::query()
            ->orderBy('session_time')
            ->pluck('session_time')
            ->map(fn ($time) => Carbon::createFromTimeString((string) $time)->format('H:i'))
            ->toArray();

        if ($slotTimes === []) {
            return ['ok' => false, 'status' => 422, 'message' => 'Sesi foto belum dikonfigurasi', 'booking' => $booking];
        }

        if (! in_array($target->format('H:i'), $slotTimes, true)) {
            return ['ok' => false, 'status' => 422, 'message' => 'Jam yang dipilih tidak tersedia', 'booking' => $booking];
        }

        // Jika booking sudah confirmed, pastikan ada assignment photographer & studio yang tersedia
        $assignment = null;
        if ($booking->status === BookingStatus::Confirmed) {
            $assignment = $this->findAvailableAssignment($target, $durationMinutes, $booking->id);
            if (! $assignment) {
                return ['ok' => false, 'status' => 422, 'message' => 'Slot tidak tersedia untuk jadwal tersebut', 'booking' => $booking];
            }
        }

        return DB::transaction(function () use ($booking, $target, $assignment, $tz) {
            $oldPhotographerId = $booking->photographer_id ? (int) $booking->photographer_id : null;

            $update = [
                'scheduled_at' => $target->copy()->setTimezone($tz),
            ];

            if ($assignment) {
                $update['photographer_id'] = $assignment['photographer_id'];
                $update['studio_id'] = $assignment['studio_id'];
            } else {
                // Pending booking: jadwal berubah, assignment dibiarkan apa adanya (biasanya null)
            }

            $booking->update($update);

            $formatted = $target->copy()->setTimezone($tz)->format('d-m-Y H:i');
            $extra = ['booking_id' => $booking->id, 'code' => $booking->code];

            $booking->user?->notify(new GenericDatabaseNotification(
                message: "Jadwal booking Anda berhasil diubah ke {$formatted}.",
                kind: NotificationType::BookingConfirmed->value,
                extra: $extra,
            ));

            if ($assignment) {
                $newPhotographer = Photographer::find($assignment['photographer_id']);
                $newPhotographer?->notify(new GenericDatabaseNotification(
                    message: "Anda ditugaskan untuk sesi foto {$booking->code} pada {$formatted}.",
                    kind: NotificationType::BookingConfirmed->value,
                    extra: $extra,
                ));

                if ($oldPhotographerId && $oldPhotographerId !== (int) $assignment['photographer_id']) {
                    $oldPhotographer = Photographer::find($oldPhotographerId);
                    $oldPhotographer?->notify(new GenericDatabaseNotification(
                        message: "Penugasan Anda untuk booking {$booking->code} telah dijadwal ulang.",
                        kind: NotificationType::BookingConfirmed->value,
                        extra: $extra,
                    ));
                }
            }

            return ['ok' => true, 'status' => 200, 'message' => 'Booking rescheduled', 'booking' => $booking];
        });
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
     * Jika slot yang diminta tidak tersedia, booking akan dibatalkan dan user diberitahu
     */
    protected function processFCFS(Booking $booking)
    {
        $booking->loadMissing(['package', 'user']);

        $tz = 'Asia/Makassar';
        $durationMinutes = (int) ($booking->package?->duration_minutes ?? 60);

        $scheduledAt = $booking->scheduled_at
            ? $booking->scheduled_at->copy()->setTimezone($tz)
            : null;

        // Jika user tidak memilih jadwal, gagalkan booking
        if (! $scheduledAt) {
            $booking->update(['status' => BookingStatus::Cancelled]);
            $booking->user?->notify(new GenericDatabaseNotification(
                message: 'Booking gagal: Jadwal tidak dipilih. Silakan buat reservasi ulang dengan memilih jadwal yang tersedia.',
                kind: NotificationType::Cancelled->value,
                extra: ['booking_id' => $booking->id, 'code' => $booking->code],
            ));

            return;
        }

        $assignment = $this->findAvailableAssignment($scheduledAt, $durationMinutes, $booking->id);

        // Jika slot yang diminta tidak tersedia, batalkan booking dan beritahu user
        if (! $assignment) {
            $requestedFormatted = $scheduledAt->format('d-m-Y H:i');
            $booking->update(['status' => BookingStatus::Cancelled]);
            $booking->user?->notify(new GenericDatabaseNotification(
                message: "Maaf, jadwal {$requestedFormatted} sudah tidak tersedia karena telah dipesan oleh pelanggan lain. Pembayaran Anda akan diproses untuk pengembalian dana. Silakan buat reservasi ulang dengan memilih jadwal lain yang tersedia.",
                kind: NotificationType::Cancelled->value,
                extra: ['booking_id' => $booking->id, 'code' => $booking->code, 'reason' => 'slot_not_available'],
            ));
            Log::warning("Booking {$booking->code} dibatalkan: slot {$requestedFormatted} tidak tersedia (race condition)");

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
            $resolvedEmail = 'guest+'.$hpDigits.'@example.test';
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
            $uniqueEmail = 'guest+'.$hpDigits.'+'.$counter.'@example.test';
            $counter++;
        }

        $uniqueHp = $hp !== '' ? $hp : $hpDigits;
        $hpCounter = 2;
        while (User::query()->where('hp', $uniqueHp)->exists()) {
            $uniqueHp = $hpDigits.'-'.$hpCounter;
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

                $scheduledAt = Carbon::parse($date->toDateString().' '.$slot['time'], $tz);
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
