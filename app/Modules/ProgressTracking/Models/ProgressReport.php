<?php

namespace App\Modules\ProgressTracking\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
{
    protected $table = 'progress_reports';

    protected $fillable = [
        'user_id',
        'total_quizzes',
        'average_score',
        'topics_studied',
        'strengths',
        'weaknesses',
        'summary',
        'generated_at'
    ];

    protected $casts = [
        'topics_studied' => 'array',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'generated_at' => 'datetime',
    ];
}
