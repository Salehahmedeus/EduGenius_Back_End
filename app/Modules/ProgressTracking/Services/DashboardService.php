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

    public function getBasicInfo($user, array $stats, $language = 'en')
    {
        // 1. Format User
        $userData = [
            'name' => $user->name,
            'avatar_initials' => strtoupper(substr($user->name, 0, 2)),
            'email' => $user->email
        ];

        // 2. Format Recent Activities
        $rawHistory = $this->repo->getRecentActivity($user->id);
        $activities = $rawHistory->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->topic ?? 'General Activity',
                'type' => $item->activity_type,
                'time_ago' => Carbon::parse($item->created_at)->diffForHumans()
            ];
        });

        // 3. Generate Smart Recommendation
        $recommendation = $this->generateRecommendation($stats, $language);

        return [
            'user' => $userData,
            'recent_activities' => $activities,
            'recommendation' => $recommendation
        ];
    }

    private function generateRecommendation($stats, $language)
    {
        $isArabic = str_starts_with($language, 'ar');

        if ($stats['uploaded_count'] == 0) {
            return [
                'text' => $isArabic ? "ابدأ بتحميل أول ملف PDF لك." : "Start by uploading your first PDF.",
                'action' => 'upload'
            ];
        }
        if ($stats['quiz_count'] > 0 && $stats['avg_score'] < 60) {
            return [
                'text' => $isArabic ? "درجاتك منخفضة. اطلب المساعدة من المعلم الذكي." : "Your scores are low. Ask the AI for help.",
                'action' => 'chat'
            ];
        }

        return [
            'text' => $isArabic ? "استمر في العمل الرائع!" : "Keep up the great work!",
            'action' => 'none'
        ];
    }
}
