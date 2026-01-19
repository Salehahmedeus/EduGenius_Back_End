<?php

namespace App\Modules\AILearning\Repositories;

use App\Modules\ContentManagement\Models\KnowledgeBase;
use App\Modules\AILearning\Models\ConversationContext;
use App\Modules\AILearning\Models\AIResponse;
use Illuminate\Support\Str;

class KnowledgeRepository
{
    /**
     * Search the student's extracted text using keywords.
     */
    public function searchByKeywords($userId, array $keywords)
    {
        if (empty($keywords)) {
            return collect([]);
        }

        return KnowledgeBase::whereHas('uploadedMaterial', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->orWhere('content_text', 'LIKE', "%{$word}%");
                }
            })
            ->limit(3)
            ->get();
    }

    /**
     * Log the conversation.
     * Handles Creating NEW chats or CONTINUING existing ones.
     */
    public function logInteraction($userId, $query, $response, $sources, $conversationId = null)
    {

        $context = null;

        // 1. Try to find existing chat if ID is provided
        if ($conversationId) {
            $context = ConversationContext::where('id', $conversationId)
                ->where('user_id', $userId)
                ->first();
        }

        // 2. If no ID provided OR ID not found -> Create NEW Chat
        if (!$context) {
            $context = ConversationContext::create([
                'user_id' => $userId,
                // Use first 30 chars of the question as the Title
                'context_name' => substr($query, 0, 30) . '...',
                'conversation_id' => Str::uuid(),
            ]);
        }

        // 3. Save the message linked to this specific context
        AIResponse::create([
            'user_id' => $userId,
            'conversation_id' => $context->id,
            'user_query' => $query,
            'ai_response' => $response,
            'confidence_score' => 0.85,
            'sources' => $sources
        ]);

        return $context; // Return the context so frontend knows the ID
    }

    /**
     * Get list of all chat sessions for the sidebar.
     */
    public function getUserConversations($userId)
    {
        return ConversationContext::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'context_name', 'created_at']);
    }

    /**
     * Delete a conversation and all its messages.
     */
    public function deleteConversation($userId, $conversationId)
    {
        // 1. Find the conversation belonging to this user
        $chat = ConversationContext::where('user_id', $userId)
            ->where('id', $conversationId)
            ->first();

        if (!$chat) {
            return false; // Chat doesn't exist or doesn't belong to user
        }

        // 2. Delete it
        // Because we defined ->onDelete('cascade') in the migration,
        // this will AUTOMATICALLY delete all related rows in 'ai_responses'.
        $chat->delete();

        return true;
    }
}
