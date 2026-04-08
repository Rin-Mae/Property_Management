<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            ->whereNotNull('payment_date')
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
                'guest_name' => $reservation->guest?->name ?? 'N/A',
                'booking_ref' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                'room_name' => $reservation->room?->name ?? 'N/A',
                'amount' => number_format($reservation->total_price, 2),
                'amount_raw' => $reservation->total_price,
                'method' => $reservation->payment_method ?? 'Not specified',
                'date' => $reservation->created_at?->format('M d, Y') ?? 'N/A',
                'date_raw' => $reservation->created_at?->format('Y-m-d') ?? 'N/A',
                'status' => $paymentStatus,
                'reservation_status' => $reservation->status,
                'check_in' => $reservation->check_in?->format('M d, Y') ?? 'N/A',
                'check_out' => $reservation->check_out?->format('M d, Y') ?? 'N/A',
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
    public function show($payment)
    {
        // Find the reservation by ID (payment is the reservation ID)
        $reservation = Reservation::with('guest', 'room')->findOrFail($payment);
        
        $paymentStatus = 'Pending';
        if (in_array($reservation->status, ['confirmed', 'checked_in', 'checked_out'])) {
            $paymentStatus = 'Paid';
        } elseif ($reservation->status === 'cancelled') {
            $paymentStatus = 'Rejected';
        }

        return response()->json([
            'id' => 'PMS-PM' . str_pad($reservation->id, 4, '0', STR_PAD_LEFT),
            'reservation_id' => $reservation->id,
            'guest_name' => $reservation->guest?->name ?? 'N/A',
            'booking_ref' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
            'room_name' => $reservation->room?->name ?? 'N/A',
            'amount' => number_format($reservation->total_price, 2),
            'amount_raw' => $reservation->total_price,
            'method' => $reservation->payment_method ?? 'Not specified',
            'date' => $reservation->created_at?->format('M d, Y') ?? 'N/A',
            'date_raw' => $reservation->created_at?->format('Y-m-d') ?? 'N/A',
            'status' => $paymentStatus,
            'reservation_status' => $reservation->status,
            'payment_date' => $reservation->payment_date?->format('M d, Y') ?? 'N/A',
            'payment_date_raw' => $reservation->payment_date?->format('Y-m-d') ?? 'N/A',
            'payment_proof' => $reservation->payment_proof,
            'check_in' => $reservation->check_in?->format('M d, Y') ?? 'N/A',
            'check_in_raw' => $reservation->check_in?->format('Y-m-d') ?? 'N/A',
            'check_out' => $reservation->check_out?->format('M d, Y') ?? 'N/A',
            'check_out_raw' => $reservation->check_out?->format('Y-m-d') ?? 'N/A',
            'notes' => $reservation->notes,
        ]);
    }

    /**
     * Approve a payment (update reservation status to confirmed)
     */
    public function approve(Request $request, $payment)
    {
        $reservation = Reservation::findOrFail($payment);
        $reservation->update(['status' => 'confirmed']);
        
        // Update room status to occupied when payment is verified
        if ($reservation->room) {
            $reservation->room->update(['status' => 'occupied']);
        }

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
    public function reject(Request $request, $payment)
    {
        $reservation = Reservation::findOrFail($payment);
        $reservation->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Payment rejected successfully',
            'data' => [
                'status' => 'Rejected',
                'reservation_status' => 'cancelled',
            ],
        ]);
    }

    /**
     * Get all payments for authenticated user
     */
    public function getUserPayments(Request $request)
    {
        $user = Auth::user();

        $payments = Reservation::where('user_id', $user->id)
            ->with(['guest', 'room.type'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reservation) {
                $paymentStatus = 'pending';
                if (in_array($reservation->status, ['confirmed', 'checked_in', 'checked_out'])) {
                    $paymentStatus = 'verified';
                }

                return [
                    'id' => $reservation->id,
                    'reference_number' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'guest_name' => $reservation->guest?->name ?? '',
                    'room' => [
                        'id' => $reservation->room?->id,
                        'name' => $reservation->room?->name ?? 'N/A',
                        'image_url' => $reservation->room?->image_url,
                        'type' => [
                            'id' => $reservation->room?->type?->id ?? null,
                            'name' => $reservation->room?->type?->name ?? 'N/A',
                        ]
                    ],
                    'check_in' => $reservation->check_in?->format('Y-m-d') ?? 'N/A',
                    'check_out' => $reservation->check_out?->format('Y-m-d') ?? 'N/A',
                    'number_of_guests' => $reservation->number_of_guests ?? 0,
                    'total_amount' => $reservation->total_price,
                    'amount' => $reservation->total_price,
                    'payment_date' => $reservation->payment_date?->format('Y-m-d'),
                    'payment_method' => $reservation->payment_method,
                    'payment_proof' => $reservation->payment_proof,
                    'status' => $paymentStatus,
                    'reservation_status' => $reservation->status,
                ];
            });

        return response()->json([
            'data' => $payments->toArray(),
        ]);
    }

    /**
     * Get pending payments for authenticated user
     */
    public function getPendingPayments(Request $request)
    {
        $user = Auth::user();

        $payments = Reservation::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['guest', 'room.type'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'reference_number' => 'PMS-' . str_pad($reservation->id, 5, '0', STR_PAD_LEFT),
                    'guest_name' => $reservation->guest?->name ?? '',
                    'room' => [
                        'id' => $reservation->room?->id,
                        'name' => $reservation->room?->name ?? 'N/A',
                        'image_url' => $reservation->room?->image_url,
                        'type' => [
                            'id' => $reservation->room?->type?->id ?? null,
                            'name' => $reservation->room?->type?->name ?? 'N/A',
                        ]
                    ],
                    'check_in' => $reservation->check_in?->format('Y-m-d') ?? 'N/A',
                    'check_out' => $reservation->check_out?->format('Y-m-d') ?? 'N/A',
                    'number_of_guests' => $reservation->number_of_guests ?? 0,
                    'total_amount' => $reservation->total_price,
                    'payment_date' => $reservation->payment_date?->format('Y-m-d'),
                    'payment_method' => $reservation->payment_method,
                    'payment_proof' => $reservation->payment_proof,
                    'status' => 'pending',
                ];
            });

        return response()->json([
            'data' => $payments->toArray(),
        ]);
    }

    /**
     * Submit payment proof by user
     */
    public function submitPayment(Request $request, Reservation $reservation)
    {
        // Verify the reservation belongs to the authenticated user
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Validation: proof_file is required only if payment method is not cash
            $rules = [
                'full_name' => 'required|string',
                'payment_method' => 'required|string|in:cash,gcash,bank_transfer',
                'payment_date' => 'required|date',
            ];
            
            // Only require proof_file if payment method is not cash
            if ($request->input('payment_method') !== 'cash') {
                $rules['proof_file'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
            } else {
                $rules['proof_file'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
            }
            
            $validated = $request->validate($rules);

            // Store the proof file only if it exists and payment method is not cash
            $proofPath = null;
            if ($request->hasFile('proof_file') && $request->input('payment_method') !== 'cash') {
                $file = $request->file('proof_file');
                $proofPath = $file->store('payment-proofs', 'public');
            }

            // Update reservation with payment details
            $reservation->update([
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'payment_proof' => $proofPath,
            ]);

            return response()->json([
                'message' => 'Payment submitted successfully',
                'data' => [
                    'id' => $reservation->id,
                    'status' => 'pending',
                    'payment_proof' => $proofPath,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error submitting payment: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update payment method (change payment method for existing payments)
     */
    public function updatePaymentMethod(Request $request, Reservation $reservation)
    {
        // Verify the reservation belongs to the authenticated user
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Validation
            $rules = [
                'payment_method' => 'required|string|in:cash,gcash,bank_transfer',
            ];
            
            // Only require proof_file if payment method is not cash
            if ($request->input('payment_method') !== 'cash') {
                $rules['proof_file'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
            } else {
                $rules['proof_file'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
            }
            
            $validated = $request->validate($rules);

            // Update proof file only if provided and payment method is not cash
            $updateData = [
                'payment_method' => $validated['payment_method'],
            ];

            if ($request->hasFile('proof_file') && $request->input('payment_method') !== 'cash') {
                $file = $request->file('proof_file');
                $proofPath = $file->store('payment-proofs', 'public');
                $updateData['payment_proof'] = $proofPath;
            } elseif ($request->input('payment_method') === 'cash') {
                // Clear proof for cash payments
                $updateData['payment_proof'] = null;
            }

            $reservation->update($updateData);

            return response()->json([
                'message' => 'Payment method updated successfully',
                'data' => [
                    'id' => $reservation->id,
                    'payment_method' => $reservation->payment_method,
                    'payment_proof' => $reservation->payment_proof,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Payment update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating payment: ' . $e->getMessage(),
            ], 500);
        }
    }
}

