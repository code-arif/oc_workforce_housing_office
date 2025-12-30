<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaseDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lease_id',
        'template_id',
        'document_url',
        'tenant_signature_token',
        'tenant_signed_at',
        'tenant_ip_address',
        'admin_signature_token',
        'admin_signed_at'
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function template()
    {
        return $this->belongsTo(LeaseTemplate::class);
    }

    
}
