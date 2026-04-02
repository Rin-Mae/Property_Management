<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    protected $fillable = [
        'name',
        'room_number',
        'type_id',
        'capacity',
        'price',
        'description',
        'status',
        'image_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the type of the room
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * Get the amenities of the room
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities');
    }

    /**
     * Get available rooms
     */
    public static function available()
    {
        return self::where('status', 'available');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeByType($query, $typeId)
    {
        return $query->where('type_id', $typeId);
    }
}
