<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admins see all reservations, users see only their own
        $query = Reservation::with('guest', 'room');
        
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        
        $bookings = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'reservation_id' => $reservation->id,
                    'guest_id' => $reservation->guest_id,
                    'guest_name' => $reservation->guest->name,
                    'room_id' => $reservation->room_id,
                    'room_name' => $reservation->room->name,
                    'check_in' => $reservation->check_in->format('M d, Y H:i'),
                    'check_out' => $reservation->check_out->format('M d, Y H:i'),
                    'check_in_time' => $reservation->check_in->format('H:i'),
                    'check_out_time' => $reservation->check_out->format('H:i'),
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

        // Update room status based on booking status
        if ($reservation->room_id) {
            $room = \App\Models\Room::find($reservation->room_id);
            if ($room) {
                if ($validated['status'] === 'checked_in') {
                    // Room is occupied when guest checks in
                    $room->update(['status' => 'occupied']);
                } elseif ($validated['status'] === 'checked_out') {
                    // Room becomes available again when guest checks out
                    $room->update(['status' => 'available']);
                }
            }
        }

        return response()->json([
            'message' => 'Booking status updated successfully',
            'data' => $reservation,
        ]);
    }

    /**
     * Store a user booking request
     */
    public function storeUserBooking(Request $request)
    {
        try {
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'full_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'check_in' => 'required|date_format:Y-m-d\TH:i:s|after_or_equal:now',
                'check_out' => 'required|date_format:Y-m-d\TH:i:s|after:check_in',
            ]);

            // Create or find guest
            $guest = Guest::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['full_name'],
                    'phone' => $validated['phone'],
                ]
            );

            // Get the room to calculate total price
            $room = \App\Models\Room::findOrFail($validated['room_id']);
            $checkIn = new \DateTime($validated['check_in']);
            $checkOut = new \DateTime($validated['check_out']);
            $nights = $checkOut->diff($checkIn)->days;
            $totalPrice = $room->price * $nights;

            // Create reservation
            $reservation = Reservation::create([
                'user_id' => Auth::id(),
                'guest_id' => $guest->id,
                'room_id' => $validated['room_id'],
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Booking request submitted successfully',
                'data' => [
                    'id' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'reservation_id' => $reservation->id,
                    'status' => $reservation->status,
                    'check_in' => $reservation->check_in->format('M d, Y H:i'),
                    'check_out' => $reservation->check_out->format('M d, Y H:i'),
                    'total_price' => $reservation->total_price,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Booking error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error creating booking: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get user's bookings
     */
    public function getUserBookings(Request $request)
    {
        $user = Auth::user();
        
        // Get all reservations for the authenticated user
        $bookings = Reservation::with('guest', 'room')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'reservation_id' => $reservation->id,
                    'guest_id' => $reservation->guest_id,
                    'guest_name' => $reservation->guest?->name ?? 'N/A',
                    'room_id' => $reservation->room_id,
                    'room_name' => $reservation->room?->name ?? 'N/A',
                    'check_in' => $reservation->check_in?->format('M d, Y H:i') ?? 'N/A',
                    'check_out' => $reservation->check_out?->format('M d, Y H:i') ?? 'N/A',
                    'check_in_time' => $reservation->check_in?->format('H:i') ?? 'N/A',
                    'check_out_time' => $reservation->check_out?->format('H:i') ?? 'N/A',
                    'status' => $reservation->status,
                    'total_price' => $reservation->total_price,
                    'notes' => $reservation->notes,
                ];
            });

        return response()->json(['data' => $bookings]);
    }

    /**
     * Get booked dates for a specific room
     * Used to disable unavailable dates in the calendar
     */
    public function getBookedDates($roomId)
    {
        try {
            $roomId = (int) $roomId;
            
            // Get all non-cancelled bookings for this room
            $bookings = Reservation::where('room_id', $roomId)
                ->where('status', '!=', 'cancelled')
                ->select('check_in', 'check_out')
                ->get();

            // Convert to array of booked date ranges
            $bookedDates = $bookings->map(function ($booking) {
                return [
                    'start' => $booking->check_in->format('Y-m-d'),
                    'end' => $booking->check_out->format('Y-m-d'),
                    'dates' => $this->getDatesBetween($booking->check_in, $booking->check_out),
                ];
            })->values();

            // Get all individual booked dates
            $allBookedDates = [];
            foreach ($bookedDates as $range) {
                $allBookedDates = array_merge($allBookedDates, $range['dates']);
            }

            return response()->json([
                'room_id' => $roomId,
                'booked_dates' => array_unique($allBookedDates),
                'booked_ranges' => $bookedDates,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching booked dates: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper function to get all dates between two dates
     */
    private function getDatesBetween($startDate, $endDate)
    {
        $dates = [];
        $current = $startDate->copy();
        
        while ($current < $endDate) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }
        
        return $dates;
    }

    /**
     * Store feedback for a completed booking
     */
    public function submitFeedback(Request $request, $bookingId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized. Please login.',
                ], 401);
            }
            
            // Extract the numeric ID from the booking ID
            $reservationId = is_numeric($bookingId) ? (int)$bookingId : (int)$bookingId;
            
            // Find the reservation
            $reservation = Reservation::find($reservationId);
            
            if (!$reservation) {
                return response()->json([
                    'message' => 'Booking not found',
                ], 404);
            }
            
            // Check if the user owns this reservation
            if ($reservation->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'message' => 'You do not have permission to submit feedback for this booking',
                ], 403);
            }
            
            // Check if the booking is completed (checked_out)
            if ($reservation->status !== 'checked_out') {
                return response()->json([
                    'message' => 'Feedback can only be submitted for completed bookings (checked out)',
                ], 422);
            }
            
            // Validate the feedback
            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comments' => 'nullable|string|max:1000',
            ]);
            
            // Check if feedback already exists
            $existingFeedback = Feedback::where('reservation_id', $reservationId)
                ->where('user_id', $user->id)
                ->first();
            
            if ($existingFeedback) {
                // Update existing feedback
                $existingFeedback->update([
                    'rating' => $validated['rating'],
                    'comments' => $validated['comments'] ?? '',
                ]);
                $feedback = $existingFeedback;
                $isNew = false;
            } else {
                // Create new feedback
                $feedback = Feedback::create([
                    'user_id' => $user->id,
                    'reservation_id' => $reservationId,
                    'rating' => $validated['rating'],
                    'comments' => $validated['comments'] ?? '',
                ]);
                $isNew = true;
            }
            
            return response()->json([
                'message' => $isNew ? 'Feedback submitted successfully' : 'Feedback updated successfully',
                'data' => $feedback,
            ], $isNew ? 201 : 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error submitting feedback: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error submitting feedback: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get feedback for a specific booking
     */
    public function getFeedback($bookingId)
    {
        try {
            // Extract the numeric ID from the booking ID
            $reservationId = is_numeric($bookingId) ? (int)$bookingId : (int)$bookingId;
            
            // Find the reservation with guest, room, and feedback relationships
            $reservation = Reservation::with(['guest', 'room', 'feedback' => function($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            }])->find($reservationId);
            
            if (!$reservation) {
                return response()->json([
                    'message' => 'Booking not found',
                ], 404);
            }
            
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }
            
            // Check authorization - admins can view all, users only their own
            if ($user->role !== 'admin' && $reservation->user_id !== $user->id) {
                return response()->json([
                    'message' => 'You do not have permission to view feedback for this booking',
                ], 403);
            }
            
            $feedback = $reservation->feedback ?? [];
            
            $guestName = 'N/A';
            $roomName = 'N/A';
            
            if ($reservation->guest) {
                $guestName = $reservation->guest->name ?? 'N/A';
            }
            
            if ($reservation->room) {
                $roomName = $reservation->room->name ?? 'N/A';
            }
            
            return response()->json([
                'data' => $feedback,
                'booking' => [
                    'id' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'guest_name' => $guestName,
                    'room_name' => $roomName,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching feedback: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching feedback: ' . $e->getMessage(),
            ], 500);
        }
    }
}

