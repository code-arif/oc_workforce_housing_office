<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequestAttachment extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array', // because you are storing multiple files
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }
}
