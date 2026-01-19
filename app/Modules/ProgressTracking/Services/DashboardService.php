<?php

namespace App\Modules\ProgressTracking\Services;

use App\Modules\ProgressTracking\Repositories\AnalyticsRepository;
use Carbon\Carbon;

class DashboardService
{
    protected $repo;

    public function __construct(AnalyticsRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Get data for the Main Home Screen.
     */
    public function getHomeData($user)
    {
        // 1. Get Summary Stats (Reusing the repo method)
        $stats = $this->repo->getSummaryStats($user->id);

        // 2. Get Recent Activity
        // We need to add a method to AnalyticsRepository for this
        $rawHistory = $this->repo->getRecentActivity($user->id);

        $recentActivities = $rawHistory->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->topic ?? 'General Study',
                'type' => $item->activity_type, // 'upload', 'quiz', 'ai_tutor'
                'time_ago' => Carbon::parse($item->created_at)->diffForHumans()
            ];
        });

        // 3. Generate Smart Recommendation
        $recommendation = $this->generateRecommendation($user->id, $stats);

        return [
            'user' => [
                'name' => $user->name,
                // Get initials (e.g. "Ahmed Saleh" -> "AS")
                'avatar_initials' => strtoupper(substr($user->name, 0, 2))
            ],
            'progress' => [
                'uploaded_count' => \App\Modules\ContentManagement\Models\UploadedMaterial::where('user_id', $user->id)->count(),
                'quiz_count' => $stats['total_quizzes'],
                'average_score' => $stats['avg_score']
            ],
            'recent_activities' => $recentActivities,
            'recommendation' => $recommendation
        ];
    }

    private function generateRecommendation($userId, $stats)
    {
        // Logic 1: New User?
        if ($stats['total_quizzes'] === 0 && $stats['total_study_sessions'] === 0) {
            return [
                'has_recommendation' => true,
                'text' => "Welcome! Start by uploading your first course material.",
                'action' => 'upload'
            ];
        }

        // Logic 2: Low Score?
        if ($stats['avg_score'] > 0 && $stats['avg_score'] < 60) {
            return [
                'has_recommendation' => true,
                'text' => "Your quiz scores are low. Try asking the AI Tutor to explain difficult concepts.",
                'action' => 'chat'
            ];
        }

        // Default
        return [
            'has_recommendation' => true,
            'text' => "You are doing great! Keep learning.",
            'action' => 'none'
        ];
    }
}
