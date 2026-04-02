<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'guest_id',
        'room_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
}
