<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingConfirmationController extends Controller
{
    /**
     * Konfirmasi kesiapan user untuk booking yang akan segera dimulai
     */
    public function confirmReadiness(Request $request, $bookingId)
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('user.login')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->where('status', BookingStatus::Confirmed)
            ->first();

        if (!$booking) {
            return redirect()->route('user.dashboard', ['tab' => 'history'])->with('error', 'Booking tidak ditemukan atau tidak dapat dikonfirmasi.');
        }

        // Cek apakah booking sudah terlalu jauh dari jadwal (misalnya lebih dari 1 jam sebelum atau sudah lewat)
        $scheduledAt = $booking->scheduled_at;
        $now = Carbon::now();
        $diffInMinutes = $now->diffInMinutes($scheduledAt, false);

        // Hanya bisa konfirmasi jika masih dalam range 60 menit sebelum sampai waktu mulai
        if ($diffInMinutes > 60 || $diffInMinutes < 0) {
            return redirect()->route('user.dashboard', ['tab' => 'history'])->with('error', 'Konfirmasi kesiapan hanya dapat dilakukan dalam 1 jam sebelum jadwal dimulai.');
        }

        // Cek apakah sudah pernah konfirmasi
        if ($booking->readiness_confirmed_at) {
            return redirect()->route('user.dashboard', ['tab' => 'history'])->with('info', 'Anda sudah mengkonfirmasi kesiapan untuk booking ini.');
        }

        // Update konfirmasi kesiapan
        $booking->update([
            'readiness_confirmed_at' => Carbon::now(),
        ]);

        return redirect()->route('user.dashboard', ['tab' => 'history'])->with('success', 'Terima kasih! Konfirmasi kesiapan Anda telah diterima.');
    }
}
