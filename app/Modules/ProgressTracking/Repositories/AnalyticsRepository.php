<?php

namespace App\Modules\ProgressTracking\Repositories;

use App\Modules\Assessment\Models\QuizResult;
use App\Modules\ContentManagement\Models\UploadedMaterial;
use App\Modules\ProgressTracking\Models\LearningHistory;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository
{
    /**
     * 1. The Master Stats (Single Query logic)
     */
    public function getUnifiedStats($userId)
    {
        return [
            'uploaded_count' => UploadedMaterial::where('user_id', $userId)->count(),
            'quiz_count'     => QuizResult::where('user_id', $userId)->count(),
            // Get average score formatted to 1 decimal place (e.g. 85.5)
            'avg_score'      => round(QuizResult::where('user_id', $userId)->avg('score') ?? 0, 1),
            'study_sessions' => LearningHistory::where('user_id', $userId)->count(),
        ];
    }

    /**
     * 2. Recent Activity Feed
     */
    public function getRecentActivity($userId)
    {
        return LearningHistory::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * 3. Chart Data (Performance Trend)
     */
    public function getPerformanceTrend($userId)
    {
        return QuizResult::where('user_id', $userId)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(score) as avg_score'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->take(10)
            ->get();
    }

    /**
     * 4. Chart Data (Topic Strengths)
     */
    public function getTopicPerformance($userId)
    {
        return DB::table('quiz_results')
            ->join('quizzes', 'quiz_results.quiz_id', '=', 'quizzes.id')
            ->where('quiz_results.user_id', $userId)
            ->select('quizzes.topic', DB::raw('AVG(quiz_results.score) as avg_score'))
            ->groupBy('quizzes.topic')
            ->orderBy('avg_score', 'desc')
            ->get();
    }

    /**
     * 5. Chart Data (Activity Pie)
     */
    public function getActivityDistribution($userId)
    {
        return LearningHistory::where('user_id', $userId)
            ->select('activity_type', DB::raw('count(*) as count'))
            ->groupBy('activity_type')
            ->get();
    }
}
