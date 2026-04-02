<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentsController extends Controller
{
    /**
     * Get all payments with filters
     */
    public function index(Request $request)
    {
        $perPage = 10;
        $page = $request->get('page', 1);
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');

        $query = Reservation::with('guest', 'room')
            ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])
            ->orderBy('created_at', 'desc');

        // Filter by payment status
        if ($status !== 'all') {
            if ($status === 'paid') {
                $query->whereIn('status', ['confirmed', 'checked_in', 'checked_out']);
            } elseif ($status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($status === 'rejected') {
                $query->where('status', 'cancelled');
            }
        }

        // Search by guest name or booking reference
        if ($search) {
            $query->whereHas('guest', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $payments = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $payments->map(function ($reservation) {
            $paymentStatus = 'Pending';
            if (in_array($reservation->status, ['confirmed', 'checked_in', 'checked_out'])) {
                $paymentStatus = 'Paid';
            } elseif ($reservation->status === 'cancelled') {
                $paymentStatus = 'Rejected';
            }

            return [
                'id' => 'PMS-PM' . str_pad($reservation->id, 4, '0', STR_PAD_LEFT),
                'reservation_id' => $reservation->id,
                'guest_name' => $reservation->guest->name,
                'booking_ref' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                'room_name' => $reservation->room->name,
                'amount' => number_format($reservation->total_price, 2),
                'amount_raw' => $reservation->total_price,
                'method' => $reservation->payment_method ?? 'Not specified',
                'date' => $reservation->created_at->format('M d, Y'),
                'date_raw' => $reservation->created_at->format('Y-m-d'),
                'status' => $paymentStatus,
                'reservation_status' => $reservation->status,
                'check_in' => $reservation->check_in->format('M d, Y'),
                'check_out' => $reservation->check_out->format('M d, Y'),
                'notes' => $reservation->notes,
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'per_page' => $payments->perPage(),
                'total' => $total,
                'last_page' => $payments->lastPage(),
            ],
        ]);
    }

    /**
     * Get payment details
     */
    public function show(Reservation $reservation)
    {
        $paymentStatus = 'Pending';
        if (in_array($reservation->status, ['confirmed', 'checked_in', 'checked_out'])) {
            $paymentStatus = 'Paid';
        } elseif ($reservation->status === 'cancelled') {
            $paymentStatus = 'Rejected';
        }

        return response()->json([
            'id' => 'PMS-PM' . str_pad($reservation->id, 4, '0', STR_PAD_LEFT),
            'reservation_id' => $reservation->id,
            'guest_name' => $reservation->guest->name,
            'booking_ref' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
            'room_name' => $reservation->room->name,
            'amount' => number_format($reservation->total_price, 2),
            'amount_raw' => $reservation->total_price,
            'method' => $reservation->payment_method ?? 'Not specified',
            'date' => $reservation->created_at->format('M d, Y'),
            'status' => $paymentStatus,
            'reservation_status' => $reservation->status,
            'payment_date' => $reservation->created_at->format('M d, Y'),
            'notes' => $reservation->notes,
        ]);
    }

    /**
     * Approve a payment (update reservation status to confirmed)
     */
    public function approve(Request $request, Reservation $reservation)
    {
        $reservation->update(['status' => 'confirmed']);

        return response()->json([
            'message' => 'Payment approved successfully',
            'data' => [
                'status' => 'Paid',
                'reservation_status' => 'confirmed',
            ],
        ]);
    }

    /**
     * Reject a payment (update reservation status to cancelled)
     */
    public function reject(Request $request, Reservation $reservation)
    {
        $reservation->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Payment rejected successfully',
            'data' => [
                'status' => 'Rejected',
                'reservation_status' => 'cancelled',
            ],
        ]);
    }
}
