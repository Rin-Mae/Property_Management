<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TORRequest extends Model
{
    protected $table = 'tor_requests';

    protected $fillable = [
        'user_id',
        'full_name',
        'birthplace',
        'birthdate',
        'student_id',
        'course',
        'degree',
        'year_of_graduation',
        'purpose',
        'number_of_copies',
        'status',
        'remarks',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the TOR request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
