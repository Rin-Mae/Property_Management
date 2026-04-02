<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'contact_number',
        'address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the full name of the client
     */
    public function getFullNameAttribute()
    {
        $name = "{$this->first_name}";
        
        if ($this->middle_name) {
            $name .= " {$this->middle_name}";
        }
        
        $name .= " {$this->last_name}";
        
        return $name;
    }
}
