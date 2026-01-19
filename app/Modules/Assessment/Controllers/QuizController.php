<?php

namespace App\Modules\Assessment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Assessment\Services\QuizGenerator;
use App\Modules\Assessment\Services\PerformanceAnalyzer;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    protected $generator;
    protected $analyzer;

    /**
     * Dependency Injection:
     * We inject both services here. Laravel automatically finds them.
     */
    public function __construct(QuizGenerator $generator, PerformanceAnalyzer $analyzer)
    {
        $this->generator = $generator;
        $this->analyzer = $analyzer;
    }

    /**
     * Endpoint: POST /api/quiz/generate
     * Description: Creates a new quiz based on the user's uploaded materials.
     */
    public function generate(Request $request)
    {
        $request->validate([
            // Accept an array of IDs (e.g., [1, 5, 8])
            'material_ids'   => 'required|array|min:1',
            'material_ids.*' => 'integer|exists:uploaded_materials,id',
            'difficulty'     => 'integer|min:1|max:3'
        ]);

        try {
            $quiz = $this->generator->generateQuiz(
                auth('api')->id(),
                $request->input('material_ids'), // Pass array
                $request->input('difficulty', 1)
            );

            return response()->json([
                'message' => 'Quiz generated successfully',
                'data' => $quiz
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint: POST /api/quiz/submit
     * Description: Grades the user's answers and saves the result.
     */
    public function submit(Request $request)
    {
        // 1. Validation
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'answers' => 'required|array'
            // Format: { "question_id_1": "User Answer A", "question_id_2": "User Answer B" }
        ]);

        try {
            // 2. Call the Analyzer Service
            $result = $this->analyzer->submitAndAnalyze(
                auth('api')->id(),
                $request->input('quiz_id'),
                $request->input('answers')
            );

            return response()->json([
                'message' => 'Quiz submitted successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
