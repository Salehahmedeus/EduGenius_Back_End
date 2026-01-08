<?php

namespace App\Modules\ContentManagement\Repositories;

use App\Modules\ContentManagement\Models\UploadedMaterial;
use App\Modules\ContentManagement\Models\KnowledgeBase;

class MaterialRepository
{
    public function createMaterial(array $data)
    {
        return UploadedMaterial::create($data);
    }

    public function saveContent($materialId, $text)
    {
        return KnowledgeBase::create([
            'material_id' => $materialId,
            'content_text' => $text,
        ]);
    }

    public function getUserFiles($userId)
    {
        return UploadedMaterial::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findMaterial($id)
    {
        return UploadedMaterial::find($id);
    }

    public function deleteMaterial($id)
    {
        // Because we used ->onDelete('cascade') in the migration,
        // deleting this will AUTOMATICALLY delete the linked KnowledgeBase entry.
        return UploadedMaterial::destroy($id);
    }

    public function searchMaterials($userId, $keyword)
    {
        return UploadedMaterial::where('user_id', $userId)
            ->where('file_name', 'LIKE', "%{$keyword}%") // % allows partial matching
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
