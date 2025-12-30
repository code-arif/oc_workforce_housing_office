<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaseTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'is_active',
    ];

    public function documents()
    {
        return $this->hasMany(LeaseDocument::class);
    }
}
