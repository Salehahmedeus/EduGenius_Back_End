<?php

namespace App\Modules\Assessment\Services;

use App\Modules\Assessment\Repositories\QuizRepository;
use App\Modules\AILearning\Repositories\KnowledgeRepository;
use Illuminate\Support\Facades\Http;

class QuizGenerator
{
    protected $quizRepo;
    protected $knowledgeRepo;

    public function __construct(QuizRepository $quizRepo, KnowledgeRepository $knowledgeRepo)
    {
        $this->quizRepo = $quizRepo;
        $this->knowledgeRepo = $knowledgeRepo;
    }

    public function generateQuiz($userId, $topic, $difficulty = 1)
    {
        // 1. Get Context from Student's Notes
        $keywords = explode(' ', $topic);
        $localResults = $this->knowledgeRepo->searchByKeywords($userId, $keywords);

        if ($localResults->isEmpty()) {
            throw new \Exception("No material found for this topic. Please upload a file first.");
        }

        $contextText = "";
        foreach ($localResults as $result) {
            $contextText .= substr($result->content_text, 0, 1000) . "\n";
        }

        // 2. Create the Quiz Session
        $quiz = $this->quizRepo->createQuiz($userId, $topic, $difficulty);

        // 3. Ask AI to generate questions
        $questions = $this->fetchQuestionsFromAI($contextText, $difficulty);

        // 4. Save Questions to DB
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

        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}";

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

        if ($response->failed()) {
            throw new \Exception("AI Generation Failed");
        }

        $data = $response->json();
        $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
        $cleanJson = str_replace(['```json', '```'], '', $rawText);

        return json_decode($cleanJson, true) ?? [];
    }
}
