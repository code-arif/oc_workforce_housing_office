<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'country_of_origin',
        'date_of_birth',
        'arrival_date',
        'departure_date',
        'passport_copy',
        'visa_document',
        'front_id_document',
        'back_id_document',
        'notes',
        'application_type',
        'application_number',
        'application_status',
        'property_id',
        'employer_info',
        'sponsor_name',
        'sponsor_city',
        'sponsor_state',
        'sponsor_zipcode',
        'sponsor_country',
        'sponsor_phone',
        'sponsor_email',
        'sponsor_relationship',
        'is_j1_sponsor',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'date_of_birth' => 'date',
        'employer_info' => 'array', 
    ];

     /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */    
    protected $hidden = [
        'passport_copy',
        'visa_document',
        'front_id_document',
        'back_id_document',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'passport_copy_url',
        'visa_document_url',
        'front_id_document_url',
        'back_id_document_url',
        'full_name',
    ];

    /**
     * Get full name of the applicant
     */
    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get the property that owns the application.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getPassportCopyUrlAttribute()
    {
        return $this->passport_copy ? asset('storage/' . $this->passport_copy) : null;
    }

    public function getVisaDocumentUrlAttribute()
    {
        return $this->visa_document ? asset('storage/' . $this->visa_document) : null;
    }

    public function getFrontIdDocumentUrlAttribute()
    {
        return $this->front_id_document ? asset('storage/' . $this->front_id_document) : null;
    }

    public function getBackIdDocumentUrlAttribute()
    {
        return $this->back_id_document ? asset('storage/' . $this->back_id_document) : null;
    }

    /**
     * Check if application is individual type
     */
    public function isIndividual()
    {
        return $this->type === 'individual';
    }

    /**
     * Check if application is corporate type
     */
    public function isCorporate()
    {
        return $this->type === 'corporate';
    }

    /**
     * Check if application is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if application is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if application is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function getReservationItemAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return $value ?? [];
    }
}
