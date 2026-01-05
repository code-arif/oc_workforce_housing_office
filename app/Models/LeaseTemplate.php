<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaseTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'document_file_path',
        'document_type',
        'uploaded_file_original_name',
        'field_mappings',
        'is_active',
        'is_editable',
    ];

    protected $casts = [
        'field_mappings' => 'json',
        'is_active' => 'boolean',
        'is_editable' => 'boolean',
    ];

    /**
     * Get all field mappings for this template
     */
    public function fieldMappings(): HasMany
    {
        return $this->hasMany(LeaseTemplateFieldMapping::class, 'template_id');
    }

    /**
     * Get all lease documents created from this template
     */
    public function documents(): HasMany
    {
        return $this->hasMany(LeaseDocument::class);
    }

    /**
     * Scope to get only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the storage path for the document file
     */
    public function getDocumentFullPath(): ?string
    {
        if (!$this->document_file_path) {
            return null;
        }

        return storage_path('app/private/' . $this->document_file_path);
    }
}
