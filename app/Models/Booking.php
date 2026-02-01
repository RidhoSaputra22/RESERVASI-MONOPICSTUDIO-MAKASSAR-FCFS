<?php

namespace App\Models;

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
        'user_id',
        'package_id',
        'photographer_id',
        'studio_id',
        'scheduled_at',
        'snap_token',
        'status',
        'code',
        'readiness_confirmed_at',
    ];

    protected $casts = [
        'status' => BookingStatus::class,
        'scheduled_at' => 'datetime',
        'readiness_confirmed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            // Ambil prefix dari nama paket, contoh "Couple" → "COUPLE"
            $package = Package::find($booking->package_id);
            $prefix = strtoupper(Str::slug($package->name, ''));
            $booking->code = $prefix . '-#' . strtoupper(Str::random(8));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
}
