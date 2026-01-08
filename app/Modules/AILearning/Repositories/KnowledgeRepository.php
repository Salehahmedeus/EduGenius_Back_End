<?php

namespace App\Modules\AILearning\Repositories;

use App\Modules\ContentManagement\Models\KnowledgeBase;
use App\Modules\AILearning\Models\ConversationContext;
use App\Modules\AILearning\Models\AIResponse;

class KnowledgeRepository
{
    /**
     * Search the student's extracted text.
     * Logic: Simple Keyword matching (LIKE query).
     */
    public function searchLocalContext($userId, $query)
    {
        // Break query into words (e.g. "What is gravity" -> ["What", "is", "gravity"])
        $keywords = explode(' ', $query);
        $significantWords = array_filter($keywords, function ($word) {
            return strlen($word) > 3; // Filter out "is", "the", "how"
        });

        if (empty($significantWords)) return collect([]);

        // Find KnowledgeBase entries belonging to this user that contain the keywords
        return KnowledgeBase::whereHas('uploadedMaterial', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where(function ($q) use ($significantWords) {
                foreach ($significantWords as $word) {
                    $q->orWhere('content_text', 'LIKE', "%{$word}%");
                }
            })
            ->limit(3) // Only get top 3 results to save tokens
            ->get();
    }

    /**
     * Save the interaction to history.
     */
    public function logInteraction($userId, $query, $response, $sources)
    {
        // 1. Get or Create a default conversation
        $context = ConversationContext::firstOrCreate(
            ['user_id' => $userId],
            ['context_name' => 'General Chat']
        );

        // 2. Save the message
        return AIResponse::create([
            'user_id' => $userId,
            'conversation_id' => $context->id,
            'user_query' => $query,
            'ai_response' => $response,
            'sources' => $sources
        ]);
    }

    public function getHistory($userId)
    {
        return AIResponse::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
    }
}
