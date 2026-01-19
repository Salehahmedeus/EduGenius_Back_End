<?php

namespace App\Modules\ProgressTracking\Services;

use App\Modules\ProgressTracking\Repositories\AnalyticsRepository;

class VisualizationService
{
    protected $repo;

    public function __construct(AnalyticsRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getFullDashboard($userId)
    {
        $trend = $this->repo->getPerformanceTrend($userId);
        $topics = $this->repo->getTopicPerformance($userId);
        $activities = $this->repo->getActivityDistribution($userId);
        $summary = $this->repo->getSummaryStats($userId);

        return [
            'summary' => $summary,
            'charts' => [
                'performance_trend' => $trend, // [{date: "2024-01-01", avg_score: 80}, ...]
                'topic_strengths' => $topics,  // [{topic: "Physics", avg_score: 90}, ...]
                'activity_breakdown' => $activities // [{activity_type: "quiz", count: 5}, ...]
            ],
            'insights' => $this->generateInsights($topics, $summary['avg_score'])
        ];
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
