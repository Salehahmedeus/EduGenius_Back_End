<?php

namespace App\Modules\ProgressTracking\Models;

use Illuminate\Database\Eloquent\Model;

class LearningHistory extends Model
{
    protected $table = 'learning_history';
    protected $fillable = ['user_id', 'activity_type', 'topic', 'time_spent', 'metadata', 'date'];
    protected $casts = ['metadata' => 'array', 'date' => 'date'];
}
