<?php

namespace App\Modules\Assessment\Services;

use App\Modules\Assessment\Repositories\QuizRepository;
use App\Modules\AILearning\Repositories\KnowledgeRepository;
// 👇 Import the AI Service
use App\Modules\AILearning\Services\OpenAIService;

class QuizGenerator
{
    protected $quizRepo;
    protected $knowledgeRepo;
    protected $aiService; // Added property

    public function __construct(
        QuizRepository $quizRepo,
        KnowledgeRepository $knowledgeRepo,
        OpenAIService $aiService // Inject 
    ) {
        $this->quizRepo = $quizRepo;
        $this->knowledgeRepo = $knowledgeRepo;
        $this->aiService = $aiService;
    }

    public function generateQuiz($userId, $topic, $difficulty = 1)
    {
        // 1. Get Context (Same as before)
        $keywords = explode(' ', $topic);
        $localResults = $this->knowledgeRepo->searchByKeywords($userId, $keywords);

        if ($localResults->isEmpty()) {
            throw new \Exception("No material found for this topic. Please upload a file first.");
        }

        $contextText = "";
        foreach ($localResults as $result) {
            $contextText .= substr($result->content_text, 0, 1000) . "\n";
        }

        // 2. Create Quiz Session
        $quiz = $this->quizRepo->createQuiz($userId, $topic, $difficulty);

        // 3. Ask AI (Now using the Service!)
        $questions = $this->fetchQuestionsFromAI($contextText, $difficulty);

        // 4. Save
        $this->quizRepo->addQuestions($quiz->id, $questions);

        return $this->quizRepo->getQuiz($quiz->id);
    }

    private function fetchQuestionsFromAI($context, $difficulty)
    {
        $diffLabel = match ((int)$difficulty) {
            1 => "Easy",
            2 => "Medium",
            default => "Hard"
        };

        $prompt = "Generate 5 multiple-choice questions based on the text below. " .
            "Difficulty: $diffLabel. " .
            "Return ONLY raw JSON (No markdown). " .
            "Format: [{ \"question\": \"...\", \"options\": [\"A\", \"B\", \"C\", \"D\"], \"correct_answer\": \"The String Answer\", \"explanation\": \"...\" }] " .
            "\n\nCONTEXT:\n" . $context;


        $rawText = $this->aiService->generateRawContent($prompt);

        if (!$rawText) {
            throw new \Exception("AI failed to generate quiz.");
        }

        // Clean up markdown if Gemini adds it (Common issue)
        $cleanJson = str_replace(['```json', '```'], '', $rawText);

        $json = json_decode($cleanJson, true);

        if (!$json) {
            // Fallback: Sometimes JSON is valid but wrapped in text. 
            // For a grad project, throwing an error is acceptable if parsing fails.
            throw new \Exception("AI returned invalid JSON format.");
        }

        return $json;
    }
}
