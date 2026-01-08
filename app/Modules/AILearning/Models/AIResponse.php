<?php

namespace App\Modules\AILearning\Models;

use Illuminate\Database\Eloquent\Model;

class AIResponse extends Model
{
    protected $table = 'ai_responses';

    protected $fillable = [
        'user_id',
        'conversation_id',
        'user_query',
        'ai_response',
        'confidence_score',
        'sources'
    ];

    protected $casts = [
        'sources' => 'array',
    ];
}
