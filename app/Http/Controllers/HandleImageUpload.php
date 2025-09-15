<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class HandleImageUpload extends Controller
{
    public static function handleImageToInsert(UploadedFile $photo)
    {
        try {
            $imageName = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
            $imageData = file_get_contents($photo->getPathname());
            $base64EncodedImage = base64_encode($imageData);
            $mimeType = $photo->getClientMimeType();

            $finalBase64String = "data:{$mimeType};base64," . $base64EncodedImage;
            return [
                "imageName" => $imageName,
                "base64Encoded" => $finalBase64String
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
