<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseTemplateFieldMapping extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'template_id',
        'field_placeholder',
        'field_label',
        'field_type',
        'tenant_data_source',
        'is_required',
        'placeholder_position',
    ];

    protected $casts = [
        'placeholder_position' => 'json',
        'is_required' => 'boolean',
    ];

    /**
     * Get the template this field mapping belongs to
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(LeaseTemplate::class);
    }

    /**
     * Get all available data sources for field mapping
     */
    public static function getAvailableDataSources(): array
    {
        return [
            'tenant' => [
                'tenant.full_name' => 'Tenant Full Name',
                'tenant.email' => 'Tenant Email',
                'tenant.phone' => 'Tenant Phone',
                'tenant.ssn' => 'Tenant SSN',
                'tenant.date_of_birth' => 'Tenant Date of Birth',
            ],
            'property' => [
                'property.address' => 'Property Address',
                'property.city' => 'Property City',
                'property.state' => 'Property State',
                'property.zip' => 'Property ZIP Code',
                'property.country' => 'Property Country',
                'property.unit_number' => 'Unit Number',
            ],
            'lease' => [
                'lease.start_date' => 'Lease Start Date',
                'lease.end_date' => 'Lease End Date',
                'lease.rent_amount' => 'Monthly Rent Amount',
                'lease.deposit_amount' => 'Security Deposit Amount',
                'lease.payment_frequency' => 'Payment Frequency',
                'lease.status' => 'Lease Status',
            ],
            'custom' => [
                'custom.field' => 'Custom Field (Manual Entry)',
            ],
        ];
    }

    /**
     * Scope to get required fields only
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope to filter by field type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('field_type', $type);
    }

    /**
     * Resolve the data source to get actual value
     * This is used during document generation
     */
    public static function resolveDataSource(string $source, $tenant = null, $property = null, $lease = null)
    {
        [$entity, $field] = explode('.', $source, 2);

        return match ($entity) {
            'tenant' => $tenant ? $tenant->{$field} : null,
            'property' => $property ? $property->{$field} : null,
            'lease' => $lease ? $lease->{$field} : null,
            'custom' => null,
            default => null,
        };
    }
}
