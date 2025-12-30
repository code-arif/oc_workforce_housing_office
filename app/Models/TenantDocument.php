<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantDocument extends Model
{
    protected $fillable = [
        'tenant_id',
        'document_type',
        'file_path',
        'file_original_name',
        'mime_type',
        'file_size'
    ];

    // reverse relation with tenant table
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
