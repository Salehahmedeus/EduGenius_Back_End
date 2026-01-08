<?php

namespace App\Modules\AILearning\Services;

use App\Modules\AILearning\Repositories\KnowledgeRepository;
use App\Modules\AILearning\Repositories\HistoryRepository;

class ResponseSynthesizer
{
    protected $knowledgeRepo;
    protected $historyRepo;
    protected $nlpProcessor;
    protected $aiService;

    public function __construct(
        KnowledgeRepository $knowledgeRepo,
        HistoryRepository $historyRepo,
        NLPProcessor $nlpProcessor,  // Inject NLP
        OpenAIService $aiService     // Inject AI
    ) {
        $this->knowledgeRepo = $knowledgeRepo;
        $this->historyRepo = $historyRepo;
        $this->nlpProcessor = $nlpProcessor;
        $this->aiService = $aiService;
    }

    public function generate($userId, $query)
    {
        // 1. NLP Processor: Extract Keywords
        // (Sequence Diagram Step: "Tokenize and analyze query")
        $keywords = $this->nlpProcessor->extractKeywords($query);

        // 2. Knowledge Repo: Search Context
        // (Sequence Diagram Step: "Search internal knowledge base")
        $localResults = $this->knowledgeRepo->searchByKeywords($userId, $keywords);

        $contextText = "";
        $sourceIds = [];
        foreach ($localResults as $result) {
            $contextText .= substr($result->content_text, 0, 800) . "\n\n";
            $sourceIds[] = $result->material_id;
        }

        // 3. OpenAI Service: Get Answer
        // (Sequence Diagram Step: "Process with GPT model")
        $systemPrompt = "You are EduGenius. Answer based on the context provided.";

        $aiText = $this->aiService->sendQuery($systemPrompt, $contextText, $query);

        if (!$aiText) {
            $aiText = "I'm having trouble connecting to the AI service.";
        }

        // 4. Log Interaction
        $this->knowledgeRepo->logInteraction($userId, $query, $aiText, $sourceIds);

        return [
            'response' => $aiText,
            'sources' => $sourceIds
        ];
    }

    public function getChatHistory($userId)
    {
        return $this->historyRepo->getUserChatHistory($userId);
    }
}
