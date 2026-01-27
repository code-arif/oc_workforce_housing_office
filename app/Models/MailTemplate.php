<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'variables',
        'description',
        'status',
    ];

    protected $casts = [
        'variables' => 'array',
        'status' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get available placeholder variables for this template
     */
    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Parse the template body with given data
     */
    public function parseBody(array $data): string
    {
        $body = $this->body;
        
        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
            $body = str_replace('{{ ' . $key . ' }}', $value, $body);
        }
        
        return $body;
    }

    /**
     * Parse the template subject with given data
     */
    public function parseSubject(array $data): string
    {
        $subject = $this->subject;
        
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $subject = str_replace('{{ ' . $key . ' }}', $value, $subject);
        }
        
        return $subject;
    }
}
