<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class ImageHelper
{
    /**
     * Store and resize an image locally using the public disk.
     * @param mixed $image File upload or binary string
     * @param string $storagePath Relative path within the public disk (e.g., 'appointment_photos')
     * @return string|null Relative path of the stored image on the public disk, or null on failure
     */
    public static function storeAndResizeLocally($image, $storagePath)
    {
        try {
            Log::info('Starting local image processing', ['storage_path' => $storagePath]);

            // Handle both file uploads and binary data
            if (is_string($image) && !is_file($image)) {
                // Binary data
                $interventionImage = Image::make($image);
                Log::info('Processing binary image data for local storage');
            } else {
                // File upload
                 if (!$image || !$image->isValid()) {
                    Log::error('Invalid image file provided for local storage.');
                    return null;
                }
                $interventionImage = Image::make($image);
                Log::info('Processing uploaded file for local storage', [
                    'original_name' => $image->getClientOriginalName() ?? 'N/A',
                ]);
            }

            // Resize the image and get the path to the temporary file
            $resizedImagePath = self::resizeAndStoreTempImage($interventionImage);
            if (!$resizedImagePath || !file_exists($resizedImagePath)) {
                 Log::error('Failed to create temporary resized image.');
                 return null;
            }


            // Generate a unique filename with .jpg extension
            $uniqueFileName = self::generateUniqueFileName() . '.jpg';

            // Define the final relative path on the public disk
            $finalRelativePath = $storagePath . '/' . $uniqueFileName;

            // Read the content of the temporary resized image
            $resizedImageContent = file_get_contents($resizedImagePath);

            // Store the resized image content to the public disk
            $stored = Storage::disk('public')->put($finalRelativePath, $resizedImageContent);

             // Delete the temporary file
            unlink($resizedImagePath);
            Log::info('Temporary file deleted', ['temp_path' => $resizedImagePath]);


            if ($stored) {
                 Log::info('Image resized and stored locally successfully', [
                    'final_path' => $finalRelativePath,
                    'disk' => 'public'
                 ]);
                 return $finalRelativePath; // Return the relative path
             } else {
                 Log::error('Failed to store resized image to public disk', ['path' => $finalRelativePath]);
                 return null;
            }

        } catch (\Exception $e) {
            Log::error('Failed to process and store image locally', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
             // Attempt to clean up temp file if it exists and error occurred after creation
            if (isset($resizedImagePath) && file_exists($resizedImagePath)) {
                unlink($resizedImagePath);
                Log::info('Cleaned up temporary file after error', ['temp_path' => $resizedImagePath]);
            }
            // Do not rethrow, return null to indicate failure as per function description
            return null;
        }
    }


    /**
     * Store and resize a profile picture
     */
    public static function storeAndResizeProfilePicture($image, $storagePath)
    {
        // Implementation of storeAndResizeProfilePicture method
    }

    /**
     * Generate a unique file name
     */
    private static function generateUniqueFileName()
    {
        // Generate a unique filename based on timestamp and random string
        return date('YmdHis') . '_' . Str::random(10);
    }

    /**
     * Resize and store a temporary image
     */
    private static function resizeAndStoreTempImage($interventionImage)
    {
        try {
            // Get original dimensions
            $originalWidth = $interventionImage->width();
            $originalHeight = $interventionImage->height();
            
            Log::info('Original image dimensions', [
                'width' => $originalWidth,
                'height' => $originalHeight
            ]);
            
            // Define target max dimensions
            $maxWidth = 1200;
            $maxHeight = 1200;
            
            // Calculate new dimensions while maintaining aspect ratio
            if ($originalWidth > $originalHeight) {
                // Landscape orientation
                $newWidth = min($originalWidth, $maxWidth);
                $newHeight = intval($originalHeight * ($newWidth / $originalWidth));
            } else {
                // Portrait orientation or square
                $newHeight = min($originalHeight, $maxHeight);
                $newWidth = intval($originalWidth * ($newHeight / $originalHeight));
            }
            
            Log::info('Resizing image to dimensions', [
                'new_width' => $newWidth,
                'new_height' => $newHeight
            ]);
            
            // Resize the image maintaining aspect ratio
            $interventionImage->resize($newWidth, $newHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // Prevent upsizing smaller images
            });
            
            // Set the image quality and format
            $interventionImage->encode('jpg', 85); // 85% quality JPEG
            
            // Create a temporary file to store the resized image
            $tempPath = tempnam(sys_get_temp_dir(), 'img_');
            $tempPathWithExt = $tempPath . '.jpg';
            rename($tempPath, $tempPathWithExt);
            
            // Save the image to the temporary file
            $interventionImage->save($tempPathWithExt);
            
            Log::info('Temporary image created', ['temp_path' => $tempPathWithExt]);
            
            return $tempPathWithExt;
            
        } catch (\Exception $e) {
            Log::error('Failed to resize and store temp image', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
} 