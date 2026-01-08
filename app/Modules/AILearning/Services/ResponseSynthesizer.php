<?php

namespace App\Modules\AILearning\Services;

use App\Modules\AILearning\Repositories\KnowledgeRepository;
use OpenAI\Laravel\Facades\OpenAI;

class ResponseSynthesizer
{
    protected $repo;

    public function __construct(KnowledgeRepository $repo)
    {
        $this->repo = $repo;
    }

    public function generate($userId, $query)
    {
        // 1. Search Local Context (Student's Notes)
        $localResults = $this->repo->searchLocalContext($userId, $query);

        $contextText = "";
        $sourceIds = [];

        foreach ($localResults as $result) {
            // Grab a chunk of text around the match (simulated here by taking first 500 chars)
            $contextText .= "--- Source (File ID: {$result->material_id}) ---\n" .
                substr($result->content_text, 0, 800) . "\n\n";
            $sourceIds[] = $result->material_id;
        }

        // 2. Build the System Prompt
        $systemMessage = "You are EduGenius, an AI Tutor. " .
            "Answer the student's question accurately. " .
            "If the provided CONTEXT contains the answer, use it and mention that it comes from their notes. " .
            "If the context is empty or irrelevant, use your own general knowledge.";

        // 3. Call OpenAI
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo', // or 'gpt-4'
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => "CONTEXT:\n{$contextText}\n\nQUESTION: {$query}"],
                ],
                'max_tokens' => 500,
            ]);

            $aiText = $response->choices[0]->message->content;

            // 4. Log to DB
            $this->repo->logInteraction($userId, $query, $aiText, $sourceIds);

            return [
                'response' => $aiText,
                'sources' => $sourceIds,
                'used_local_context' => !empty($contextText)
            ];
        } catch (\Exception $e) {
            return [
                'response' => "I am currently unavailable. Please check your internet connection or API key.",
                'error' => $e->getMessage()
            ];
        }
    }

    public function getChatHistory($userId)
    {
        return $this->repo->getHistory($userId);
    }
}
