<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the rooms with this amenity
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_amenities');
    }
}
