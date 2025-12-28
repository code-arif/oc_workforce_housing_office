<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = [
        'name',
        'url',
        'icon',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Available social platforms
    public static function getPlatforms(): array
    {
        return [
            'facebook' => 'Facebook',
            'twitter' => 'Twitter/X',
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'pinterest' => 'Pinterest',
            'whatsapp' => 'WhatsApp'
        ];
    }

    public function getIconAttribute($value): string | null
    {
        if (empty($value)) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Return full URL for API requests
        if (request()->is('api/*')) {
            return url($value);
        }

        return $value;
    }
}
