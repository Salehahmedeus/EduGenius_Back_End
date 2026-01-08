<?php

namespace App\Modules\ContentManagement\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $table = 'knowledge_base'; // Explicit table name
    protected $fillable = ['material_id', 'content_text', 'vectors'];

    protected $casts = [
        'vectors' => 'array',
    ];

    // Relationship back to the file info
    public function uploadedMaterial()
    {
        return $this->belongsTo(UploadedMaterial::class, 'material_id');
    }
}
