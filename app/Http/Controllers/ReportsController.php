<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Get general reports data
     */
    public function index()
    {
        return response()->json([
            'message' => 'Reports API',
            'endpoints' => [
                '/api/reports/summary' => 'Get dashboard summary statistics',
                '/api/reports/booking-history' => 'Get booking history with pagination',
                '/api/reports/payment-history' => 'Get payment history with pagination',
            ]
        ]);
    }

    /**
     * Get summary statistics for the dashboard
     */
    public function getSummary()
    {
        $user = Auth::user();
        $query = new Reservation();
        
        // Filter by user if not admin
        if ($user && $user->role !== 'admin') {
            $baseQuery = Reservation::where('user_id', $user->id);
        } else {
            $baseQuery = Reservation::query();
        }
        
        // Total bookings (all reservations)
        $totalBookings = $baseQuery->count();
        
        // Total revenue (sum of total_price for confirmed, checked_in, or checked_out reservations)
        $totalRevenue = $baseQuery->clone()
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->sum('total_price');
        
        // Approved bookings (confirmed, checked_in, or checked_out)
        $approvedBookings = $baseQuery->clone()
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->count();
        
        // Pending payments (reservations with status pending and no payment_date)
        $pendingPayments = $baseQuery->clone()
            ->where('status', 'pending')
            ->whereNull('payment_date')
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
        
        // Filter by user if not admin
        $user = Auth::user();
        if ($user && $user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

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
                'guest_name' => $reservation->guest->name ?? 'N/A',
                'room_name' => $reservation->room->name ?? 'N/A',
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
        
        // Filter by user if not admin
        $user = Auth::user();
        if ($user && $user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

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

    /**
     * Get user-specific reports (bookings and payments)
     */
    public function getUserReports(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $bookingPage = $request->get('bookingPage', 1);
            $paymentPage = $request->get('paymentPage', 1);
            $perPage = 5;
            
            // Get user's bookings with pagination
            $bookingsQuery = Reservation::with('room.type')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');
            
            $allBookingsForStats = $bookingsQuery->get();
            $bookingsPaginated = $bookingsQuery->paginate($perPage, ['*'], 'page', $bookingPage);
            
            // Get user's payments (all reservations they've made) with pagination
            $paymentsQuery = Reservation::with('room')
                ->where('user_id', $user->id)
                ->whereIn('status', ['confirmed', 'checked_in', 'checked_out', 'pending', 'cancelled'])
                ->orderBy('created_at', 'desc');
            
            $paymentsPaginated = $paymentsQuery->paginate($perPage, ['*'], 'page', $paymentPage);
            
            // Calculate statistics from all data
            $totalBookings = $allBookingsForStats->count();
            $confirmedBookings = $allBookingsForStats->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->count();
            $cancelledBookings = $allBookingsForStats->whereIn('status', ['cancelled', 'rejected'])->count();
            $totalPayments = $allBookingsForStats->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])->sum('total_price') ?? 0;
            
            // Format booking data with proper null checks
            $formattedBookings = $bookingsPaginated->map(function ($reservation) {
                $roomType = 'N/A';
                if ($reservation->room && $reservation->room->type) {
                    $roomType = $reservation->room->type->name;
                }
                
                $checkIn = 'N/A';
                $checkOut = 'N/A';
                
                if ($reservation->check_in) {
                    $checkIn = $reservation->check_in instanceof \DateTime 
                        ? $reservation->check_in->format('M d, Y')
                        : \Carbon\Carbon::parse($reservation->check_in)->format('M d, Y');
                }
                
                if ($reservation->check_out) {
                    $checkOut = $reservation->check_out instanceof \DateTime 
                        ? $reservation->check_out->format('M d, Y')
                        : \Carbon\Carbon::parse($reservation->check_out)->format('M d, Y');
                }
                
                return [
                    'id' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'reference_no' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'room_type' => $roomType,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => ucfirst($reservation->status),
                    'created_at' => $reservation->created_at ? $reservation->created_at->format('M d, Y') : 'N/A',
                ];
            })->values();
            
            // Format payment data with proper null checks
            $formattedPayments = $paymentsPaginated->map(function ($reservation) {
                $paymentStatus = 'Pending';
                if (in_array($reservation->status, ['confirmed', 'checked_in', 'checked_out'])) {
                    $paymentStatus = 'Paid';
                } elseif (in_array($reservation->status, ['cancelled', 'rejected'])) {
                    $paymentStatus = 'Rejected';
                }
                
                $roomName = $reservation->room ? $reservation->room->name : 'N/A';
                $amount = $reservation->total_price ? number_format($reservation->total_price, 2) : '0.00';
                
                return [
                    'id' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'reference_no' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'room_name' => $roomName,
                    'amount' => $amount,
                    'payment_date' => $reservation->created_at ? $reservation->created_at->format('M d, Y') : 'N/A',
                    'status' => $paymentStatus,
                    'created_at' => $reservation->created_at ? $reservation->created_at->format('M d, Y') : 'N/A',
                ];
            })->values();
            
            return response()->json([
                'stats' => [
                    'totalBookings' => $totalBookings,
                    'totalPayments' => $totalPayments,
                    'confirmedBookings' => $confirmedBookings,
                    'cancelledBookings' => $cancelledBookings,
                ],
                'bookings' => $formattedBookings,
                'bookingsPagination' => [
                    'current_page' => $bookingsPaginated->currentPage(),
                    'per_page' => $bookingsPaginated->perPage(),
                    'total' => $bookingsPaginated->total(),
                    'last_page' => $bookingsPaginated->lastPage(),
                ],
                'payments' => $formattedPayments,
                'paymentsPagination' => [
                    'current_page' => $paymentsPaginated->currentPage(),
                    'per_page' => $paymentsPaginated->perPage(),
                    'total' => $paymentsPaginated->total(),
                    'last_page' => $paymentsPaginated->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error loading report data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}