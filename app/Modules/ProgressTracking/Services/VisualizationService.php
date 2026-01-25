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
            $prompt = "قم بتحليل أداء الطالب التالي تحليلاً أكاديمياً دقيقاً:
            متوسط الدرجات: $avgScore%
            عدد الاختبارات: $quizCount
            أقوى موضوع: $bestTopic
            
            قم بإنشاء 3 ملاحظات تحليلية أكاديمية (بحد أقصى 15 كلمة لكل منها). ركز على الفجوات المعرفية ومؤشرات الإتقان.
            أرجع فقط مصفوفة JSON من النصوص. مثال: [\"يظهر الطالب إتقاناً عالياً في الجبر، لكن هناك ضعف ملحوظ في الهندسة.\", \"معدل الأداء يشير إلى حاجة لمراجعة المفاهيم الأساسية.\"]";
        } else {
            $prompt = "Analyze this student's performance with strict academic rigor: 
            Average Score: $avgScore%
            Total Quizzes: $quizCount
            Strongest Topic: $bestTopic
            
            Generate 3 analytical, academic observations (max 15 words each). Focus on mastery gaps and performance trends.
            Return ONLY a JSON array of strings. Example: [\"Student demonstrates mastery in Algebra, but shows a conceptual gap in Geometry.\", \"Performance trend indicates a need for reinforced study in basic concepts.\"]";
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
