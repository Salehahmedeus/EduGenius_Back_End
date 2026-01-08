<?php

namespace App\Modules\AILearning\Models;

use Illuminate\Database\Eloquent\Model;

class AITutor extends Model
{
    protected $table = 'ai_tutors';

    protected $fillable = ['name', 'model_version', 'system_prompt', 'is_active'];

    // Relationship defined in your PDF (Section 2.5.2.2)
    // AITutor "generates" AIResponses
    public function responses()
    {
        return $this->hasMany(AIResponse::class, 'ai_tutor_id');
    }
}
