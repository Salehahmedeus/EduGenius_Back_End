<?php

namespace App\Modules\Assessment\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\ApiResponseTrait;
use App\Modules\Assessment\Services\QuizGenerator;
use App\Modules\Assessment\Services\PerformanceAnalyzer;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private QuizGenerator $quizGenerator,
        private PerformanceAnalyzer $performanceAnalyzer,
    ) {
    }

    public function start(Request $request)
    {
    }

    public function submit(Request $request)
    {
    }
}
