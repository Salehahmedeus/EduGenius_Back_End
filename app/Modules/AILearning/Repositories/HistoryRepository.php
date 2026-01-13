<?php

namespace App\Modules\AILearning\Repositories;

use App\Modules\AILearning\Models\AIResponse;

class HistoryRepository
{
    /**
     * Retrieve the last N messages to build conversation context.
     * (Used by the AI to "remember" context)
     */
    public function getConversationContext($userId, $conversationId = null, $limit = 5)
    {
        if (!$conversationId) return ""; // New chat = No history

        $history = AIResponse::where('user_id', $userId)
            ->where('conversation_id', $conversationId) // Filter by specific chat
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
     * Get all messages for a specific chat room.
     * Used by the Mobile App when opening a chat.
     */
    public function getMessagesByConversation($userId, $conversationId)
    {
        return AIResponse::where('user_id', $userId)
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc') // Oldest first (like WhatsApp)
            ->get();
    }
}
