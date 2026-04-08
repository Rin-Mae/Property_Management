<?php

namespace App\Rules;

use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoOverlappingBookings implements ValidationRule
{
    protected $roomId;
    protected $checkInDate;
    protected $checkOutDate;
    protected $excludeReservationId;

    /**
     * Create a new rule instance
     *
     * @param int $roomId
     * @param string|null $checkInDate
     * @param string|null $checkOutDate
     * @param int|null $excludeReservationId ID of reservation to exclude (for updates)
     */
    public function __construct($roomId, $checkInDate = null, $checkOutDate = null, $excludeReservationId = null)
    {
        $this->roomId = $roomId;
        $this->checkInDate = $checkInDate;
        $this->checkOutDate = $checkOutDate;
        $this->excludeReservationId = $excludeReservationId;
    }

    /**
     * Run the validation rule
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If we have both dates, check for overlaps
        if ($this->checkInDate && $this->checkOutDate) {
            if (Reservation::hasOverlappingBookings(
                $this->roomId,
                $this->checkInDate,
                $this->checkOutDate,
                $this->excludeReservationId
            )) {
                $fail("This room is already booked for the selected dates.");
            }
        }
    }
}
