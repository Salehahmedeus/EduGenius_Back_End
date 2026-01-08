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
}
