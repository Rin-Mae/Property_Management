<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'reservation_id',
        'rating',
        'comments',
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that submitted the feedback
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reservation this feedback is for
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
