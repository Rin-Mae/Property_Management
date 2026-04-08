<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'guest_id',
        'room_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'payment_method',
        'payment_date',
        'payment_proof',
        'number_of_guests',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'payment_date' => 'date',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * Get the user who made this reservation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the guest for this reservation
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the room for this reservation
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the feedback for this reservation
     */
    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'reservation_id');
    }

    /**
     * Check if there are overlapping bookings for the same room
     * Excludes the current reservation from the check (useful for updates)
     *
     * @param int $roomId
     * @param \Carbon\Carbon|string $checkIn
     * @param \Carbon\Carbon|string $checkOut
     * @param int|null $excludeReservationId ID of reservation to exclude from check (for updates)
     * @return bool true if there's an overlap, false otherwise
     */
    public static function hasOverlappingBookings($roomId, $checkIn, $checkOut, $excludeReservationId = null)
    {
        $query = self::where('room_id', $roomId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($checkIn, $checkOut) {
                // Overlap condition: new_check_in < existing_check_out AND new_check_out > existing_check_in
                $q->whereDate('check_in', '<', $checkOut)
                  ->whereDate('check_out', '>', $checkIn);
            });

        // If updating, exclude the current reservation
        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->exists();
    }

    /**
     * Get all overlapping bookings for this reservation
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverlappingBookings()
    {
        return Reservation::where('room_id', $this->room_id)
            ->where('id', '!=', $this->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereDate('check_in', '<', $this->check_out)
                  ->whereDate('check_out', '>', $this->check_in);
            })
            ->get();
    }
}
