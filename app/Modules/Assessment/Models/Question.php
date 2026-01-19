<?php

namespace App\Modules\Assessment\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['quiz_id', 'question_text', 'options', 'correct_answer', 'explanation'];

    protected $casts = [
        'options' => 'array', // Automatically convert JSON to Array
    ];
}
