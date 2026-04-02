<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings
     */
    public function index()
    {
        $bookings = Reservation::with('guest', 'room')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'reservation_id' => $reservation->id,
                    'guest_id' => $reservation->guest_id,
                    'guest_name' => $reservation->guest->name,
                    'room_id' => $reservation->room_id,
                    'room_name' => $reservation->room->name,
                    'check_in' => $reservation->check_in->format('M d, Y'),
                    'check_out' => $reservation->check_out->format('M d, Y'),
                    'guests' => 1, // default guests count
                    'status' => $reservation->status,
                    'total_price' => $reservation->total_price,
                    'notes' => $reservation->notes,
                ];
            });

        return response()->json(['data' => $bookings]);
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
        ]);

        $reservation->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Booking status updated successfully',
            'data' => $reservation,
        ]);
    }
}
