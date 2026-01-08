<?php

namespace App\Modules\AILearning\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    /**
     * Sends the prompt to the LLM (Gemini acting as OpenAI).
     * Matches PDF Section 2.4.3 (External Service).
     */
    public function sendQuery(string $systemPrompt, string $userContext, string $question)
    {
        $apiKey = env('GEMINI_API_KEY');
        // Using Gemini Pro 1.5 Latest
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}";

        $fullText = $systemPrompt . "\n\nCONTEXT:\n" . $userContext . "\n\nQUESTION: " . $question;

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => [['text' => $fullText]]]]
                ]);

            if ($response->failed()) {
                return null;
            }

            return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            \Log::error("AI Service Error: " . $e->getMessage());
            return null;
        }
    }
}
