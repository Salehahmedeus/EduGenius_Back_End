<?php

namespace App\Modules\AILearning\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationContext extends Model
{
    protected $table = 'conversation_contexts';

    protected $fillable = ['user_id', 'context_name'];

    public function messages()
    {
        return $this->hasMany(AIResponse::class, 'conversation_id');
    }
}
