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

        $imageName = time() . '-' . Str::random(5) . '.' . $file->extension(); // Unique name
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

     /**
     * Helper function to calculate age from date_of_birth
     */
    public static function calculateAge($date_of_birth): int
    {
        if (!$date_of_birth) {
            return 0;
        }

        $dob = new DateTime($date_of_birth);
        $now = new DateTime();
        return $now->diff($dob)->y;
    }
}
