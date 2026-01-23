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
        // Return the Quiz with its questions and the user's result (if any)
        return Quiz::with(['questions', 'result'])->find($id);
    }





    public function saveResult($userId, $quizId, $score, $total, $correct, $details = null)
    {
        return QuizResult::create([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'score' => $score,
            'total_questions' => $total,
            'correct_answers' => $correct,
            'attempt_details' => $details
        ]);
    }

    public function markCompleted($quizId)
    {
        Quiz::where('id', $quizId)->update(['is_completed' => true]);
    }

    /**
     * Get ALL quizzes (Completed and Pending).
     */
    public function getAllQuizzes($userId)
    {
        return Quiz::where('user_id', $userId)
            ->with(['result', 'questions'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
