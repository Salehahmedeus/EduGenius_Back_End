<?php

namespace App\Modules\Assessment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Assessment\Services\QuizGenerator;
use App\Modules\Assessment\Services\PerformanceAnalyzer;
use App\Modules\Assessment\Repositories\QuizRepository;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    protected $generator;
    protected $analyzer;
    protected $quizRepo;

    /**
     * Dependency Injection:
     * We inject both services here. Laravel automatically finds them.
     */
    public function __construct(QuizGenerator $generator, PerformanceAnalyzer $analyzer, QuizRepository $quizRepo)
    {
        $this->generator = $generator;
        $this->analyzer = $analyzer;
        $this->quizRepo = $quizRepo;
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

        // Get Language from Header (Default to 'en' if missing)
        $lang = $request->header('Accept-Language', 'en');

        try {
            $quiz = $this->generator->generateQuiz(
                auth('api')->id(),
                $request->input('material_ids'), // Pass array
                $request->input('difficulty', 1),
                $lang  // Pass language
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

    /**
     * Endpoint: GET /api/quiz/all
     */
    public function index()
    {
        try {
            $quizzes = $this->analyzer->getAllQuizzes(auth('api')->id());
            return response()->json($quizzes);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $quiz = $this->quizRepo->getQuiz($id);
            if (!$quiz) {
                return response()->json(['error' => 'Quiz not found'], 404);
            }
            return response()->json($quiz);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
