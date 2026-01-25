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

    public function getVisuals($userId, array $stats)
    {
        // 1. Fetch Chart Data
        $trend = $this->repo->getPerformanceTrend($userId);
        $topics = $this->repo->getTopicPerformance($userId);
        $activities = $this->repo->getActivityDistribution($userId);

        // 2. Format Charts
        $charts = [
            'performance_trend' => $trend,
            'topic_strengths' => $topics,
            'activity_breakdown' => $activities
        ];

        // 3. Generate Insights using the passed Stats + Topic data
        $insights = $this->generateInsights($stats, $topics);

        return [
            'charts' => $charts,
            'insights' => $insights
        ];
    }

    private function generateInsights($stats, $topics)
    {
        $insights = [];

        // Insight based on global average
        if ($stats['avg_score'] >= 80) {
            $insights[] = "Overall Performance: Excellent (${stats['avg_score']}%)";
        }

        // Insight based on specific topics
        if ($topics->isNotEmpty()) {
            $best = $topics->first();
            $insights[] = "Strongest Topic: {$best->topic} ({$best->avg_score}%)";
        }

        if (empty($insights)) {
            $insights[] = "Take more quizzes to see detailed insights.";
        }

        return $insights;
    }
}
