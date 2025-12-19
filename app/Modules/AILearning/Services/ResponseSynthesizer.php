<?php

namespace App\Modules\AILearning\Services;

use App\Modules\AILearning\Repositories\KnowledgeRepository;
use App\Modules\AILearning\Repositories\HistoryRepository;

class ResponseSynthesizer
{
    public function __construct(
        private OpenAIService $openAIService,
        private KnowledgeRepository $knowledgeRepository,
        private HistoryRepository $historyRepository,
        private NLPProcessor $nlpProcessor,
    ) {
    }
}
