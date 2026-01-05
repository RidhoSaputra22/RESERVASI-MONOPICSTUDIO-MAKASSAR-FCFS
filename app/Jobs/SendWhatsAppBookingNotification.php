<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\WhatsAppServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWhatsAppBookingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $bookingId)
    {
    }

    public function handle(): void
    {
        $booking = Booking::query()
            ->with(['user', 'package'])
            ->find($this->bookingId);

        if (! $booking) {
            Log::warning('WhatsApp job: booking not found', ['booking_id' => $this->bookingId]);
            return;
        }

        $userEmail = $booking->user?->email;
        $bookingCode = $booking->code;
        $paketSlug = $booking->package?->slug;

        if (! is_string($userEmail) || $userEmail === '' || ! is_string($bookingCode) || $bookingCode === '' || ! is_string($paketSlug) || $paketSlug === '') {
            Log::warning('WhatsApp job: missing required booking data', [
                'booking_id' => $booking->id,
                'user_email' => $userEmail,
                'booking_code' => $bookingCode,
                'paket_slug' => $paketSlug,
            ]);
            return;
        }

        WhatsAppServices::sendMessage($userEmail, $bookingCode, $paketSlug);
    }

    public function failed(Throwable $e): void
    {
        Log::error('Failed to send WhatsApp message (queued job)', [
            'booking_id' => $this->bookingId,
            'error' => $e->getMessage(),
        ]);
    }
}
