<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Enums\BookingStatus;
use App\Traits\HasCodeGenerated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    //
    use HasFactory, HasCodeGenerated;

    protected $fillable = [
        'customer_id',
        'package_id',
        'photographer_id',
        'studio_id',
        'scheduled_at',
        'snap_token',
        'status',
        'code',
    ];

    protected $casts = [
        'status' => BookingStatus::class,
        'scheduled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            // Ambil prefix dari nama paket, contoh "Couple" → "COUPLE"
            $package = Package::find($booking->package_id);
            $prefix = strtoupper(Str::slug($package->name, ''));
            $booking->code = self::generateCode($prefix);
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    public function studio()
    {
        return $this->belongsTo(Studio::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public static function getAvailableTimeSlots(
        int $packageId,
        ?string $date,
        int $durationMinutes,
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

        $takenIntervals = self::query()
            ->where('package_id', $packageId)
            ->whereDate('scheduled_at', $date)
            ->with('package')
            ->get()
            ->map(function (self $booking) use ($durationMinutes, $tz) {
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
            ->map(function (string $time) use ($date, $durationMinutes, $operationalStart, $operationalEnd, $takenIntervals, $tz) {
                $slotStart = Carbon::parse("$date $time", $tz);
                $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

                if ($slotStart->lt($operationalStart) || $slotEnd->gt($operationalEnd)) {
                    return [
                        'time' => $time,
                        'available' => false,
                    ];
                }

                $isTaken = $takenIntervals->contains(function (array $taken) use ($slotStart, $slotEnd) {
                    $takenStart = $taken['start'];
                    $takenEnd = $taken['end'];

                    return $slotStart->lt($takenEnd) && $slotEnd->gt($takenStart);
                });

                return [
                    'time' => $time,
                    'available' => ! $isTaken,
                ];
            })
            ->values()
            ->toArray();
    }
}
