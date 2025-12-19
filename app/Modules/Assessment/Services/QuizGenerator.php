<?php

namespace App\Modules\Assessment\Services;

use App\Modules\Assessment\Repositories\QuizRepository;

class QuizGenerator
{
    public function __construct(private QuizRepository $quizRepository)
    {
    }
}
