<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantEmploymentHistory extends Model
{
    protected $fillable = [
        'tenant_id',
        'employment_status',
        'employer',
        'title',
        'contact_person_name',
        'contact_email',
        'contact_phone',
        'is_current_working',
        'school',
        'start_date',
        'graduation_date',
    ];


    // reverse relation with tenant table
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
