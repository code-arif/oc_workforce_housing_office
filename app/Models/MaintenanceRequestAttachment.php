<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequestAttachment extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'attachment_path',
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
