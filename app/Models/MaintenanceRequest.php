<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggableActivity;

class MaintenanceRequest extends Model
{
    use SoftDeletes, LoggableActivity;

    protected $fillable = [
        'tenant_id',
        'property_id',
        'unit_id',
        'room_id',
        'bed_id',
        'unit',
        'title',
        'category',
        'description',
        'is_urgent',
        'grant_permission',
        'status',
        'created_by',
    ];

    protected $casts = [
        'is_urgent'       => 'boolean',
        'grant_permission' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Tenant who created the request
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Property where issue exists
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // RELATION: with unit model
    public function unitModel()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    // RELATION: with room model
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // RELATION: with bed model
    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    // Admin / Owner who created it
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Multiple images / videos
    public function attachments()
    {
        return $this->hasMany(MaintenanceRequestAttachment::class);
    }
}
