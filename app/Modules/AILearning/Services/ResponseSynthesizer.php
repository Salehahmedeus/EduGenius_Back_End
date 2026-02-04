<?php

namespace App\Modules\AILearning\Services;

use App\Modules\AILearning\Repositories\KnowledgeRepository;
use App\Modules\AILearning\Repositories\HistoryRepository;
use App\Modules\AILearning\Services\OpenAIService;

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

    public function generate($userId, $query, $conversationId = null, $language = 'en')
    {
        // 1. NLP & Keyword Extraction (Existing logic)
        $keywords = $this->nlpProcessor->extractKeywords($query);

        // 2. Search Local Files (Existing logic)
        $localResults = $this->knowledgeRepo->searchByKeywords($userId, $keywords);

        $fileContext = "";
        $sourceIds = [];
        foreach ($localResults as $result) {
            $fileContext .= substr($result->content_text, 0, 800) . "\n\n";
            $sourceIds[] = $result->material_id;
        }

        // 3. Get History for THIS specific conversation
        $chatHistory = $this->historyRepo->getConversationContext($userId, $conversationId);

        // 4. Send to AI (Pass language)
        $aiText = $this->aiService->sendQueryWithHistory($fileContext, $chatHistory, $query, $language);

        // 5. Log Interaction (Pass the ID)
        $context = $this->knowledgeRepo->logInteraction($userId, $query, $aiText, $sourceIds, $conversationId);

        return [
            'conversation_id' => $context->id, // Return ID so frontend can continue this chat
            'context_name' => $context->context_name,
            'response' => $aiText,
            'sources' => $sourceIds
        ];
    }

    public function getChatMessages($userId, $conversationId)
    {
        return $this->historyRepo->getMessagesByConversation($userId, $conversationId);
    }

    public function generateFromSpecificText($userId, $query, $textFromPdf, $conversationId = null, $language = 'en')
    {
        // 1. Get History (Context)
        $chatHistory = $this->historyRepo->getConversationContext($userId, $conversationId);

        // 2. Send to AI
        // We pass the PDF text directly as context
        $aiText = $this->aiService->sendQueryWithHistory(
            $textFromPdf,  //  Using the file text directly
            $chatHistory,
            $query,
            $language  // Pass language parameter
        );

        // 3. Log Interaction (Passing the conversationId to keep the thread alive)
        // We use a special source tag 'direct_file' so we know it came from a temp file
        $context = $this->knowledgeRepo->logInteraction($userId, $query, $aiText, ['direct_file_upload'], $conversationId);

        return [
            'conversation_id' => $context->id, // Return ID so they can reply
            'response' => $aiText,
            'source' => 'Direct File Upload'
        ];
    }

    public function deleteChat($userId, $conversationId)
    {
        return $this->knowledgeRepo->deleteConversation($userId, $conversationId);
    }

    public function generateNormalAi($userId, $query, $conversationId = null, $language = 'en')
    {
        // 1. NLP & Keyword Extraction (Existing logic)
        $keywords = $this->nlpProcessor->extractKeywords($query);

        // 2. Search Local Files (Existing logic)
        $localResults = $this->knowledgeRepo->searchByKeywords($userId, $keywords);

        

        // 3. Get History for THIS specific conversation
        $chatHistory = $this->historyRepo->getConversationContext($userId, $conversationId);

        // 4. Send to AI (Pass language)
        $aiText = $this->aiService->Noraml_way($chatHistory, $query, $language);

        // 5. Log Interaction (Pass the ID)
        $context = $this->knowledgeRepo->logInteraction($userId, $query, $aiText, $sourceIds, $conversationId);

        return [
            'conversation_id' => $context->id, // Return ID so frontend can continue this chat
            'context_name' => $context->context_name,
            'response' => $aiText,
            'sources' => $sourceIds
        ];
    }

}
