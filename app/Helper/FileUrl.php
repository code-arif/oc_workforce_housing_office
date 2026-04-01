<?php

namespace App\Helper;

use Illuminate\Support\Facades\Storage;

class FileUrl
{
    public static function resolve(?string $path, string $disk = 'public'): ?string
    {
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Storage::disk($disk)->url($path);
    }
}
