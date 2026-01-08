<?php

namespace App\Modules\ContentManagement\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileStorageService
{
    /**
     * Store the file physically on the disk.
     * Returns the relative path.
     */
    public function store(UploadedFile $file, $userId): string
    {
        // Generates a unique filename
        $filename = time() . '_' . $file->getClientOriginalName();

        // Save to 'storage/app/public/uploads/{user_id}'
        return $file->storeAs("uploads/{$userId}", $filename, 'public');
    }

    /**
     * Delete a file from the disk.
     */
    public function delete(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Get the full system path (needed for PDF Parser).
     */
    public function getAbsolutePath(string $relativePath): string
    {
        return storage_path('app/public/' . $relativePath);
    }
}
