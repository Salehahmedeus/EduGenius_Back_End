<?php

namespace App\Modules\Assessment\Services;

use App\Modules\Assessment\Repositories\QuizRepository;

class PerformanceAnalyzer
{
    protected $quizRepo;

    public function __construct(QuizRepository $quizRepo)
    {
        $this->quizRepo = $quizRepo;
    }

    /**
     * Calculate score and save the result.
     */
    public function submitAndAnalyze($userId, $quizId, array $userAnswers)
    {
        $quiz = $this->quizRepo->getQuiz($quizId);

        if (!$quiz) {
            throw new \Exception("Quiz not found");
        }

        if ($quiz->is_completed) {
            throw new \Exception("This quiz has already been completed.");
        }

        $correctCount = 0;
        $total = $quiz->questions->count();
        $details = [];

        // Grading Logic
        foreach ($quiz->questions as $question) {
            $userAns = $userAnswers[$question->id] ?? null;
            $isCorrect = false;

            // Compare strings (trim whitespace/lowercase to be safe)
            if ($userAns && strtolower(trim($userAns)) === strtolower(trim($question->correct_answer))) {
                $correctCount++;
                $isCorrect = true;
            }

            $details[] = [
                'question_id' => $question->id,
                'is_correct' => $isCorrect,
                'correct_answer' => $question->correct_answer,
                'explanation' => $question->explanation
            ];
        }

        $score = ($total > 0) ? ($correctCount / $total) * 100 : 0;

        // Save Result
        $this->quizRepo->saveResult($userId, $quizId, $score, $total, $correctCount);
        $this->quizRepo->markCompleted($quizId);

        // Record to History (Progress Tracking)
        // You can inject HistoryRepository here if you want to log it

        return [
            'score' => $score,
            'correct_answers' => $correctCount,
            'total_questions' => $total,
            'feedback' => $this->generateFeedback($score),
            'details' => $details
        ];
    }

    /**
     * Simple feedback logic based on score.
     */
    private function generateFeedback($score)
    {
        if ($score >= 90) return "Excellent! You have mastered this topic.";
        if ($score >= 70) return "Good job! A little more revision will help.";
        return "You might need to review your notes on this topic.";
    }
}
