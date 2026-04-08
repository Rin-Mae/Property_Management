# Overlapping Bookings Prevention

This guide explains how to prevent 2 bookings in the same room from overlapping in the Hotel Management system.

## Functions Available

### 1. Static Method: `hasOverlappingBookings()`

Check if a specific room has overlapping bookings:

```php
use App\Models\Reservation;

// Check if room 5 has any bookings between April 1-5, 2026
$hasOverlap = Reservation::hasOverlappingBookings(
    roomId: 5,
    checkIn: '2026-04-01',
    checkOut: '2026-04-05'
);

if ($hasOverlap) {
    // Room is already booked during these dates
}

// For updates, exclude the current reservation from the check
$hasOverlap = Reservation::hasOverlappingBookings(
    roomId: 5,
    checkIn: '2026-04-01',
    checkOut: '2026-04-05',
    excludeReservationId: 10  // Exclude reservation ID 10 from the check
);
```

### 2. Instance Method: `getOverlappingBookings()`

Get all overlapping bookings for a specific reservation:

```php
$reservation = Reservation::find(10);

// Get all overlapping bookings for this reservation
$overlappingBookings = $reservation->getOverlappingBookings();

foreach ($overlappingBookings as $booking) {
    echo "Overlapping booking: {$booking->id} from {$booking->check_in} to {$booking->check_out}";
}
```

## Automatic Prevention (Model Boot)

The `Reservation` model automatically prevents overlapping bookings when saving:

```php
$reservation = new Reservation([
    'room_id' => 5,
    'check_in' => '2026-04-01',
    'check_out' => '2026-04-05',
    // ... other fields
]);

try {
    $reservation->save();
} catch (\Exception $e) {
    // Exception message: "This room is already booked for the selected dates."
}
```

## Using Custom Validation Rule in Controllers

Use the `NoOverlappingBookings` custom rule in your controller validation:

```php
use App\Rules\NoOverlappingBookings;

$validated = $request->validate([
    'room_id' => 'required|exists:rooms,id',
    'check_in' => [
        'required',
        'date',
        new NoOverlappingBookings(
            roomId: $request->room_id,
            checkInDate: $request->check_in,
            checkOutDate: $request->check_out,
            excludeReservationId: $reservationId  // Optional, for updates
        )
    ],
    'check_out' => 'required|date|after:check_in',
    // ... other fields
]);
```

## How Overlap Detection Works

Bookings are considered overlapping if:

- New check-in is BEFORE existing check-out, AND
- New check-out is AFTER existing check-in

Example:

```
Existing: [April 1 -------- April 5]
New:              [April 4 -------- April 8]  ❌ OVERLAP

Existing: [April 1 -------- April 5]
New:                             [April 5 -------- April 8]  ✅ NO OVERLAP (check-in on check-out date is allowed)

Existing: [April 1 -------- April 5]
New:      [March 25 -------- April 1]  ✅ NO OVERLAP
```

## Cancelled Reservations

The overlap detection automatically excludes cancelled reservations, so a room can be re-booked once a reservation is marked as cancelled.

## Implementation Notes

- Uses database date comparison for accuracy
- Excludes cancelled reservations from overlap checks
- When updating, pass `excludeReservationId` to allow the same reservation to keep its dates
- Works with both string dates and Carbon date instances
