<?php

namespace App\Modules\AILearning\Services;

class NLPProcessor
{
    /**
     * Extracts significant keywords from the user's query.
     * Matches PDF Section 2.4.3 (NLP Processing).
     */
    public function extractKeywords(string $query): array
    {
        // 1. Remove punctuation
        $cleanQuery = preg_replace('/[^\w\s]/', '', strtolower($query));

        // 2. Tokenize (Split by space)
        $words = explode(' ', $cleanQuery);

        // 3. Filter Stop Words (Simple Implementation)
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'what', 'how', 'to'];

        return array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
    }

    /**
     * Analyze Intent (Simple rule-based for now).
     */
    public function analyzeIntent(string $query): string
    {
        if (str_contains(strtolower($query), 'quiz')) return 'generate_quiz';
        if (str_contains(strtolower($query), 'summarize')) return 'summary';
        return 'qa'; // Question Answering
    }
}
