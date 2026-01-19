<?php

namespace App\Modules\ProgressTracking\Repositories;

use App\Modules\Assessment\Models\QuizResult;
use App\Modules\ProgressTracking\Models\LearningHistory;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository
{
    /**
     * Chart 1: Line Chart (Scores over Time)
     * Groups quiz scores by date.
     */
    public function getPerformanceTrend($userId)
    {
        return QuizResult::where('user_id', $userId)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(score) as avg_score'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->take(30) // Last 30 days
            ->get();
    }

    /**
     * Chart 2: Radar/Bar Chart (Strengths & Weaknesses)
     * Groups scores by Topic.
     */
    public function getTopicPerformance($userId)
    {
        // Join quiz_results -> quizzes to get the topic name
        return DB::table('quiz_results')
            ->join('quizzes', 'quiz_results.quiz_id', '=', 'quizzes.id')
            ->where('quiz_results.user_id', $userId)
            ->select('quizzes.topic', DB::raw('AVG(quiz_results.score) as avg_score'))
            ->groupBy('quizzes.topic')
            ->orderBy('avg_score', 'desc')
            ->get();
    }

    /**
     * Chart 3: Pie Chart (Activity Distribution)
     * Counts how many times user did each activity.
     */
    public function getActivityDistribution($userId)
    {
        return LearningHistory::where('user_id', $userId)
            ->select('activity_type', DB::raw('count(*) as count'))
            ->groupBy('activity_type')
            ->get();
    }

    /**
     * Headline Stats (Top Cards)
     */
    public function getSummaryStats($userId)
    {
        return [
            'total_quizzes' => QuizResult::where('user_id', $userId)->count(),
            'avg_score' => round(QuizResult::where('user_id', $userId)->avg('score') ?? 0, 1),
            'total_study_sessions' => LearningHistory::where('user_id', $userId)->count(),
        ];
    }
}
