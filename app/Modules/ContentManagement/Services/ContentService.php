<?php

namespace App\Modules\ContentManagement\Services;

use App\Modules\ContentManagement\Repositories\MaterialRepository;
use Illuminate\Http\UploadedFile;

class ContentService
{
    protected $repo;
    protected $storageService;
    protected $processor;

    public function __construct(
        MaterialRepository $repo,
        FileStorageService $storageService, //  Inject Storage
        FileProcessor $processor             //  Inject Processor
    ) {
        $this->repo = $repo;
        $this->storageService = $storageService;
        $this->processor = $processor;
    }

    public function processUpload(UploadedFile $file, $userId)
    {
        // 1. Use StorageService to save the file
        $relativePath = $this->storageService->store($file, $userId);

        // 2. Create Initial DB Record
        $material = $this->repo->createMaterial([
            'user_id' => $userId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $relativePath,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'upload_status' => 'processing',
        ]);

        try {
            // 3. Use StorageService to get absolute path
            $fullPath = $this->storageService->getAbsolutePath($relativePath);

            // 4. Use Processor to read text
            $text = $this->processor->extractText($fullPath, $file->getMimeType());

            // 5. Save Text to DB
            $this->repo->saveContent($material->id, $text);

            // 6. Update Status
            $material->update(['upload_status' => 'completed']);

            return $material;
        } catch (\Exception $e) {
            $material->update(['upload_status' => 'failed']);
            throw $e;
        }
    }

    public function deleteMaterial($materialId, $userId)
    {
        $material = $this->repo->findMaterial($materialId);

        if (!$material) {
            throw new \Exception("File not found");
        }

        // Security Check: Does this file belong to this user?
        if ($material->user_id !== $userId) {
            throw new \Exception("Unauthorized access to this file");
        }

        // 1. Delete Physical File from Disk
        $this->storageService->delete($material->file_path);

        // 2. Delete Database Record
        $this->repo->deleteMaterial($materialId);

        return true;
    }

    public function searchUserFiles($userId, $keyword)
    {

        if (empty($keyword) || strlen($keyword) < 2) {
            return []; // Return empty if search term is too short
        }

        return $this->repo->searchMaterials($userId, $keyword);
    }
    /**
     * Get all files for a specific user.
     */
    public function listUserFiles($userId)
    {
        return $this->repo->getUserFiles($userId);
    }
}
