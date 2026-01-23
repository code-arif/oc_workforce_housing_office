<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'subject', 'message', 'status'
    ];

    // Optional helper accessor
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
