<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Get summary statistics for the dashboard
     */
    public function getSummary()
    {
        // Total bookings (all time)
        $totalBookings = Reservation::count();
        
        // Total revenue (all confirmed, checked_in, checked_out reservations)
        $totalRevenue = Reservation::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->sum('total_price');
        
        // Approved bookings (confirmed, checked_in, checked_out)
        $approvedBookings = Reservation::whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->count();
        
        // Pending payments (pending status)
        $pendingPayments = Reservation::where('status', 'pending')
            ->count();

        return response()->json([
            'total_bookings' => $totalBookings,
            'total_revenue' => number_format($totalRevenue, 2),
            'approved_bookings' => $approvedBookings,
            'pending_payments' => $pendingPayments,
        ]);
    }

    /**
     * Get booking history with filters
     */
    public function getBookingHistory(Request $request)
    {
        $perPage = 5;
        $page = $request->get('page', 1);
        $status = $request->get('status', 'all');
        $month = $request->get('month', 'all');

        $query = Reservation::with('guest', 'room')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by month
        if ($month !== 'all') {
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $total = $query->count();
        $bookings = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $bookings->map(function ($reservation) {
            return [
                'id' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                'reservation_id' => $reservation->id,
                'room_type' => $reservation->room->type->name ?? 'N/A',
                'check_in' => $reservation->check_in->format('M d, Y'),
                'check_out' => $reservation->check_out->format('M d, Y'),
                'check_in_raw' => $reservation->check_in->format('Y-m-d'),
                'check_out_raw' => $reservation->check_out->format('Y-m-d'),
                'status' => $reservation->status,
                'date_booked' => $reservation->created_at->format('M d, Y'),
                'total_price' => $reservation->total_price,
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'per_page' => $bookings->perPage(),
                'total' => $total,
                'last_page' => $bookings->lastPage(),
            ],
        ]);
    }

    /**
     * Get payment history with filters
     */
    public function getPaymentHistory(Request $request)
    {
        $perPage = 5;
        $page = $request->get('page', 1);
        $status = $request->get('status', 'all');
        $month = $request->get('month', 'all');

        $query = Reservation::with('guest', 'room')
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out', 'cancelled'])
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

        // Filter by month
        if ($month !== 'all') {
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
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
                'id' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                'reservation_id' => $reservation->id,
                'room_name' => $reservation->room->name,
                'amount' => number_format($reservation->total_price, 2),
                'amount_raw' => $reservation->total_price,
                'payment_date' => $reservation->created_at->format('M d, Y'),
                'payment_date_raw' => $reservation->created_at->format('Y-m-d'),
                'status' => $paymentStatus,
                'booking_date' => $reservation->created_at->format('M d, Y'),
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
     * Get bookings per month for chart
     */
    public function getBookingsChart(Request $request)
    {
        $fromDate = $request->get('from', Carbon::now()->subMonths(4)->startOfMonth());
        $toDate = $request->get('to', Carbon::now()->endOfMonth());

        if (is_string($fromDate)) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDate);
        }
        if (is_string($toDate)) {
            $toDate = Carbon::createFromFormat('Y-m-d', $toDate);
        }

        // Get bookings grouped by month
        $bookings = Reservation::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Generate all months in range
        $allMonths = [];
        $current = $fromDate->copy();
        while ($current <= $toDate) {
            $allMonths[$current->format('Y-m')] = 0;
            $current->addMonth();
        }

        // Fill in actual data
        foreach ($bookings as $booking) {
            $allMonths[$booking->month] = $booking->count;
        }

        // Format for chart
        $labels = [];
        $data = [];
        foreach ($allMonths as $month => $count) {
            $labels[] = Carbon::createFromFormat('Y-m', $month)->format('M');
            $data[] = $count;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Get available months from reservations
     */
    public function getAvailableMonths()
    {
        $months = Reservation::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, DATE_FORMAT(created_at, '%B %Y') as label")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->pluck('label', 'month')
            ->toArray();

        return response()->json([
            'months' => $months,
        ]);
    }
}
