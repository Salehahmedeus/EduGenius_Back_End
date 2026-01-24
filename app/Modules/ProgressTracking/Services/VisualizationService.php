<?php

namespace App\Modules\ProgressTracking\Services;

use App\Modules\ProgressTracking\Repositories\AnalyticsRepository;
use Illuminate\Support\Facades\Cache;

class VisualizationService
{
    protected $repo;

    public function __construct(AnalyticsRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getFullDashboard($userId)
    {
        // Cache this result for 60 seconds
        // Key is unique per user: "dashboard_stats_12"
        return Cache::remember("dashboard_stats_{$userId}", 60, function () use ($userId) {

            // This heavy logic only runs once per minute now!
            $trend = $this->repo->getPerformanceTrend($userId);
            $topics = $this->repo->getTopicPerformance($userId);
            $activities = $this->repo->getActivityDistribution($userId);
            $summary = $this->repo->getSummaryStats($userId);

            return [
                'summary' => $summary,
                'charts' => [
                    'performance_trend' => $trend,
                    'topic_strengths' => $topics,
                    'activity_breakdown' => $activities
                ],
                'insights' => $this->generateInsights($topics, $summary['avg_score'])
            ];
        });
    }

    /**
     * AI-like Logic: Generate text advice based on data.
     */
    private function generateInsights($topics, $avgScore)
    {
        $insights = [];

        // 1. General Health
        if ($avgScore > 85) {
            $insights[] = "You are performing excellently! Keep it up.";
        } elseif ($avgScore < 50) {
            $insights[] = "Try reviewing your uploaded materials before taking more quizzes.";
        }

        // 2. Specific Topics
        if ($topics->isNotEmpty()) {
            $best = $topics->first();
            $worst = $topics->last();

            $insights[] = "Your strongest subject is **{$best->topic}** ({$best->avg_score}%).";

            if ($worst->avg_score < 60) {
                $insights[] = "You should focus more on **{$worst->topic}**. Try asking the AI Tutor for help with this topic.";
            }
        } else {
            $insights[] = "Complete more quizzes to unlock detailed insights.";
        }

        return $insights;
    }
}
