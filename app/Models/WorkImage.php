<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkImage extends Model
{
    protected $fillable = [
        'work_id',
        'image_path'
    ];
}
