<?php

namespace App\Modules\AILearning\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Modules\AILearning\Models\AITutor;
use Illuminate\Http\Client\Response;

class OpenAIService
{
    public function sendQueryWithHistory(string $fileContext, string $chatHistory, string $question, string $language = 'en')
    {
        // ... (Get Tutor Config from DB logic) ...
        $tutor = AITutor::where('is_active', true)->first();
        $model = $tutor ? $tutor->model_version : 'gemini-1.5-flash-latest';
        $systemPrompt = $tutor ? $tutor->system_prompt : 'You are a helpful AI.';

        // Add language-specific instruction
        if (str_starts_with($language, 'ar')) {
            $systemPrompt .= "\n\nIMPORTANT INSTRUCTION: You MUST answer the user's question in Arabic Language (اللغة العربية).";
        } else {
            $systemPrompt .= "\n\nAnswer in English.";
        }

        // ... (Prepare URL) ...
        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Construct the FULL Prompt with History
        $fullText = $systemPrompt . "\n\n" .
            "RELEVANT FILE CONTENT:\n" . $fileContext . "\n\n" .
            "PREVIOUS CONVERSATION:\n" . $chatHistory . "\n\n" . //  The History
            "CURRENT QUESTION: " . $question;

        try {
            /** @var Response $response */
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => [['text' => $fullText]]]]
                ]);

            if ($response->failed()) {
                return "AI Error: " . $response->body();
            }

            return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            Log::error("AI Service Error: " . $e->getMessage());
            return null;
        }
    }

    public function generateRawContent(string $prompt, string $language = 'en')
    {
        $tutor = AITutor::where('is_active', true)->first();
        $model = $tutor ? $tutor->model_version : 'gemini-1.5-flash-latest';
        $apiKey = env('GEMINI_API_KEY');
        // We use the same model, but we might want to ensure it's the latest
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Add language-specific instruction to the prompt
        if (str_starts_with($language, 'ar')) {
            $prompt .= "\n\nIMPORTANT: Generate all content in Arabic Language (اللغة العربية).";
        } else {
            $prompt .= "\n\nGenerate all content in English.";
        }

        try {
            /** @var Response $response */
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);

            if ($response->failed()) {
                Log::error("Gemini JSON Generation Failed: " . $response->body());
                throw new \Exception("AI Service Error");
            }

            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function Noraml_way(string $chatHistory, string $question, string $language = 'en')
    {
        // ... (Get Tutor Config from DB logic) ...
        $tutor = AITutor::where('is_active', true)->first();
        $model = $tutor ? $tutor->model_version : 'gemini-1.5-flash-latest';

        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Construct the FULL Prompt with History
        $fullText = $systemPrompt . "\n\n" .
            "RELEVANT FILE CONTENT:\n" . $fileContext . "\n\n" .
            "PREVIOUS CONVERSATION:\n" . $chatHistory . "\n\n" . //  The History
            "CURRENT QUESTION: " . $question;

        try {
            /** @var Response $response */
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => [['text' => $fullText]]]]
                ]);

            if ($response->failed()) {
                return "AI Error: " . $response->body();
            }

            return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            Log::error("AI Service Error: " . $e->getMessage());
            return null;
        }
    }
}
