<?php

namespace App\Modules\Assessment\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $fillable = ['user_id', 'quiz_id', 'score', 'total_questions', 'correct_answers'];
}
