<?php

namespace App\Helper;

use DateTime;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class Helper
{
    //upload image
    public static function uploadImage($file, $folder)
    {

        if (!$file->isValid()) {
            return null;
        }

        $imageName = time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension(); // Unique name
        $path = public_path('uploads/' . $folder);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $imageName);
        return 'uploads/' . $folder . '/' . $imageName;
    }

    //delete image
    public static function deleteImage($imageUrl)
    {
        if (!$imageUrl) {
            return false;
        }
        $filePath = public_path($imageUrl);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    // file upload helper funciton
    public static function fileUpload($file, string $folder, string $name): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        // FIX: always use original extension
        $ext = strtolower($file->getClientOriginalExtension());

        // FIX: fallback for HEIC / HEIF / QuickTime
        if (!$ext) {
            $mime = $file->getMimeType();
            $map = [
                'image/heic' => 'heic',
                'image/heif' => 'heif',
                'video/quicktime' => 'mov',
            ];
            $ext = $map[$mime] ?? 'bin';
        }

        // FIX: DO NOT slug extension
        $filename = Str::slug(pathinfo($name, PATHINFO_FILENAME)) . '.' . $ext;

        $path = public_path("uploads/$folder");
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file->move($path, $filename);

        return "uploads/$folder/$filename";
    }
}
