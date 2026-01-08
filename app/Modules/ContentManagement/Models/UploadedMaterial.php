<?php

namespace App\Modules\ContentManagement\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedMaterial extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'upload_status'
    ];

    // Relationship to the text content
    public function knowledgeBase()
    {
        return $this->hasOne(KnowledgeBase::class, 'material_id');
    }
}
