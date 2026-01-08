<?php

namespace App\Modules\AILearning\Repositories;

use App\Modules\AILearning\Models\AIResponse;

class HistoryRepository
{
    /**
     * Retrieve the last N messages to build conversation context.
     * (Used by the AI to "remember" context)
     */
    public function getConversationContext($userId, $limit = 5)
    {
        $history = AIResponse::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->reverse();

        $contextString = "";

        foreach ($history as $interaction) {
            $contextString .= "Student: " . $interaction->user_query . "\n";
            $contextString .= "AI Tutor: " . $interaction->ai_response . "\n";
        }

        return $contextString;
    }

    /**
     * Get raw history list for the Mobile UI.
     * 👇 RENAMED THIS METHOD to match the Service call.
     */
    public function getUserChatHistory($userId)
    {
        return AIResponse::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
