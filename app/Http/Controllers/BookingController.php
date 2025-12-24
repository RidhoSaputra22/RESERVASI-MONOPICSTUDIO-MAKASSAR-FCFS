<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReservationService;

class BookingController extends Controller
{
    //

    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'package_id' => 'required|exists:packages,id',
        ]);

        $result = $this->reservationService->createReservation($data);

        return response()->json([
            'message' => 'Booking berhasil dibuat',
            'snap_token' => $result['snap_token'],
            'booking' => $result['booking'],
        ]);
    }

    public function callback(Request $request)
    {
        return $this->reservationService->handlePaymentCallback($request->all());
    }
}