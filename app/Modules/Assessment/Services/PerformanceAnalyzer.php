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
                'selected_option' => $userAns,
                'correct_answer' => $question->correct_answer,
                'explanation' => $question->explanation
            ];
        }

        $score = ($total > 0) ? ($correctCount / $total) * 100 : 0;

        // Save Result
        $this->quizRepo->saveResult($userId, $quizId, $score, $total, $correctCount, $details);
        $this->quizRepo->markCompleted($quizId);

        // Record to History (Progress Tracking)
        // You can inject HistoryRepository here if you want to log it

        return [
            'score' => $score,
            'correct_answers' => $correctCount,
            'total_questions' => $total,
            'feedback' => $this->generateFeedback($score, $quiz->difficulty),
            'details' => $details
        ];
    }

    /**
     * Simple feedback logic based on score.
     */
    private function generateFeedback($score, $currentDifficulty)
    {
        // Adaptive Recommendation Logic
        if ($score >= 80) {
            if ($currentDifficulty < 3) {
                return "Excellent! You are ready to try the next difficulty level.";
            } else {
                return "Masterful! You have conquered this topic.";
            }
        }

        if ($score < 50) {
            if ($currentDifficulty > 1) {
                return "Don't worry. Try switching to an easier difficulty to build confidence.";
            }
        }

        return "Good practice. Review the materials and try again.";
    }

    public function getAllQuizzes($userId)
    {
        $quizzes = $this->quizRepo->getAllQuizzes($userId);

        return $quizzes->map(function ($quiz) {
            return [
                'id' => $quiz->id,
                'topic' => $quiz->topic,
                'difficulty' => $quiz->difficulty,
                'status' => $quiz->is_completed ? 'Completed' : 'Pending',
                'score' => $quiz->result ? $quiz->result->score : null,
                'date' => $quiz->created_at->format('Y-m-d H:i'),
                // We pass the raw questions so Flutter can count them (length)
                'questions' => $quiz->questions
            ];
        });
    }
}
