<?php

namespace App\Modules\ProgressTracking\Services;

use App\Modules\ProgressTracking\Repositories\AnalyticsRepository;

class VisualizationService
{
    protected $repo;
    protected $aiService;

    public function __construct(AnalyticsRepository $repo, \App\Modules\AILearning\Services\OpenAIService $aiService)
    {
        $this->repo = $repo;
        $this->aiService = $aiService;
    }

    public function getVisuals($userId, array $stats, $language = 'en')
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

        // 3. Generate Insights (Try AI first, Fallback to Static)
        try {
            $insights = $this->generateAIInsights($stats, $topics, $language);
        } catch (\Exception $e) {
            // Fallback if AI fails
            $insights = $this->generateStaticInsights($stats, $topics);
        }

        return [
            'charts' => $charts,
            'insights' => $insights
        ];
    }

    private function generateAIInsights($stats, $topics, $language)
    {
        $avgScore = $stats['avg_score'];
        $quizCount = $stats['quiz_count'];
        $bestTopic = $topics->first() ? $topics->first()->topic : 'None';

        if (str_starts_with($language, 'ar')) {
            $prompt = "قم بتحليل أداء الطالب التالي:
            متوسط الدرجات: $avgScore%
            عدد الاختبارات: $quizCount
            أقوى موضوع: $bestTopic
            
            قم بإنشاء 3 رؤى قصيرة ومحفزة (بحد أقصى 10 كلمات لكل منها).
            أرجع فقط مصفوفة JSON من النصوص. مثال: [\"عمل رائع في الجبر!\", \"حاول تحسين الهندسة.\"]";
        } else {
            $prompt = "Analyze this student's performance: 
            Average Score: $avgScore%
            Total Quizzes: $quizCount
            Strongest Topic: $bestTopic
            
            Generate 3 short, motivating, bullet-point insights (max 10 words each). 
            Return ONLY a JSON array of strings. Example: [\"Great work on Algebra!\", \"Try to improve Geometry.\"]";
        }

        // Call AI Service with Language
        $rawJson = $this->aiService->generateRawContent($prompt, $language);

        if (!$rawJson) {
            throw new \Exception("AI returned empty response");
        }

        $cleanJson = str_replace(['```json', '```'], '', $rawJson);
        $insights = json_decode($cleanJson, true);

        if (!is_array($insights)) {
            throw new \Exception("Invalid JSON from AI");
        }

        return $insights;
    }

    private function generateStaticInsights($stats, $topics)
    {
        $insights = [];

        // Insight based on global average
        if ($stats['avg_score'] >= 80) {
            $insights[] = "Overall Performance: Excellent ({$stats['avg_score']}%)";
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
