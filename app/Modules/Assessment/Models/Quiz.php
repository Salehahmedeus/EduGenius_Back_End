<?php

namespace App\Modules\Assessment\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['user_id', 'topic', 'difficulty', 'is_completed'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function result()
    {
        return $this->hasOne(QuizResult::class);
    }
}
