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

    public function generateQuiz($userId, array $materialIds, $difficulty = 1, $language = 'en')
    {
        // 1. Get All Selected Materials
        $materials = \App\Modules\ContentManagement\Models\UploadedMaterial::with('knowledgeBase')
            ->whereIn('id', $materialIds)
            ->where('user_id', $userId) // Security Check
            ->get();

        if ($materials->isEmpty()) {
            throw new \Exception("No valid materials found.");
        }

        // 2. Combine Context & Create Topic Name
        $fullContext = "";
        $topicNames = [];

        foreach ($materials as $file) {
            if ($file->knowledgeBase) {
                // Add header so AI knows which file this text comes from
                $fullContext .= "\n--- SOURCE: {$file->file_name} ---\n";
                // Take first 10,000 chars per file to stay within safe limits
                $fullContext .= substr($file->knowledgeBase->content_text, 0, 10000) . "\n";

                $topicNames[] = $file->file_name;
            }
        }

        if (empty($fullContext)) {
            throw new \Exception("The selected files have no readable text.");
        }

        // Create a readable topic string (e.g. "File A, File B...")
        // Limit length to avoid DB errors
        $topicString = implode(', ', $topicNames);
        if (strlen($topicString) > 250) {
            $topicString = substr($topicString, 0, 247) . '...';
        }

        // 3. Create Quiz Session
        $quiz = $this->quizRepo->createQuiz($userId, $topicString, $difficulty);

        // 4. Ask AI (Send the combined context with language)
        $questions = $this->fetchQuestionsFromAI($fullContext, $difficulty, $language);

        // 5. Save Questions
        $this->quizRepo->addQuestions($quiz->id, $questions);

        return $this->quizRepo->getQuiz($quiz->id);
    }

    private function fetchQuestionsFromAI($context, $difficulty, $language = 'en')
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


        $rawText = $this->aiService->generateRawContent($prompt, $language);

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
