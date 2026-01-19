<?php

namespace App\Modules\Assessment\Repositories;

use App\Modules\Assessment\Models\Quiz;
use App\Modules\Assessment\Models\Question;
use App\Modules\Assessment\Models\QuizResult;

class QuizRepository
{
    public function createQuiz($userId, $topic, $difficulty)
    {
        return Quiz::create([
            'user_id' => $userId,
            'topic' => $topic,
            'difficulty' => $difficulty
        ]);
    }

    public function addQuestions($quizId, array $questionsData)
    {
        foreach ($questionsData as $q) {
            Question::create([
                'quiz_id' => $quizId,
                'question_text' => $q['question'],
                'options' => $q['options'],
                'correct_answer' => $q['correct_answer'],
                'explanation' => $q['explanation'] ?? null
            ]);
        }
    }

    public function getQuiz($id)
    {
        return Quiz::with('questions')->find($id);
    }

    public function saveResult($userId, $quizId, $score, $total, $correct)
    {
        return QuizResult::create([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'score' => $score,
            'total_questions' => $total,
            'correct_answers' => $correct
        ]);
    }

    public function markCompleted($quizId)
    {
        Quiz::where('id', $quizId)->update(['is_completed' => true]);
    }
}
