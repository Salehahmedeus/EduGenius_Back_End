<?php

namespace App\Modules\ProgressTracking\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Modules\ProgressTracking\Repositories\AnalyticsRepository;
use App\Modules\ProgressTracking\Services\DashboardService;
use App\Modules\ProgressTracking\Services\VisualizationService;
use App\Modules\ProgressTracking\Models\ProgressReport;

class AnalyticsController extends Controller
{
    protected $repo;
    protected $dashboardService;
    protected $vizService;
    protected $aiService;

    /**
     * Dependency Injection:
     * We inject the Repository (for raw data) and both Services (for formatting).
     */
    public function __construct(
        AnalyticsRepository $repo,
        DashboardService $dashboardService,
        VisualizationService $vizService,
        \App\Modules\AILearning\Services\OpenAIService $aiService // Inject AI
    ) {
        $this->repo = $repo;
        $this->dashboardService = $dashboardService;
        $this->vizService = $vizService;
        $this->aiService = $aiService;
    }

    /**
     * Endpoint: GET /api/dashboard/home
     * Description: Returns the "Master JSON" for the Home Screen.
     */
    public function home(Request $request)
    {
        $user = auth('api')->user();
        $userId = $user->id;

        // 0. Get Language (Default 'en')
        $lang = $request->header('Accept-Language', 'en');

        // 1. Fetch Stats ONCE (Source of Truth)
        // We get the numbers here and pass them down to avoid recalculating
        $stats = $this->repo->getUnifiedStats($userId);

        // 2. Get Basic Info (User Profile, Recents List, Recommendation)
        $basicInfo = $this->dashboardService->getBasicInfo($user, $stats, $lang);

        // 3. Get Visuals (Charts, Insights - PASS Language)
        $visuals = $this->vizService->getVisuals($userId, $stats, $lang);

        // 4. Construct Final JSON manually to match your exact requirement
        return response()->json([
            'user'              => $basicInfo['user'],
            'stats'             => $stats,
            'recommendation'    => $basicInfo['recommendation'],
            'recent_activities' => $basicInfo['recent_activities'],
            'charts'            => $visuals['charts'],
            'insights'          => $visuals['insights'],
        ]);
    }

    /**
     * Endpoint: POST /api/dashboard/report
     * Description: Generates a static snapshot (Progress Report) and saves it to DB.
     */
    public function generateReport(Request $request)
    {
        try {
            $userId = auth('api')->id();

            // Get Language (Default 'en')
            $lang = $request->header('Accept-Language', 'en');

            // Fetch raw data needed for the report
            $stats = $this->repo->getUnifiedStats($userId);
            $topicStats = $this->repo->getTopicPerformance($userId); // Need raw topic collection

            // Calculate Strengths & Weaknesses
            $strengths = $topicStats->where('avg_score', '>=', 80)->pluck('topic')->toArray();
            $weaknesses = $topicStats->where('avg_score', '<', 60)->pluck('topic')->toArray();
            $allTopics = $topicStats->pluck('topic')->toArray();

            // === Generate AI Summary (Academic Tone) ===
            $avgScore = $stats['avg_score'];
            $quizCount = $stats['quiz_count'];
            $strengthStr = implode(', ', $strengths);
            $weaknessStr = implode(', ', $weaknesses);

            if (str_starts_with($lang, 'ar')) {
                $prompt = "قم بإنشاء ملخص أكاديمي رسمي (من 20-30 كلمة) لتقرير تقدم الطالب.
                البيانات: متوسط الدرجات: $avgScore%، عدد الاختبارات: $quizCount.
                نقاط القوة: $strengthStr. نقاط الضعف: $weaknessStr.
                استخدم نبرة تحليلية موضوعية. ركز على الإنجاز ومجالات التحسين.";
            } else {
                $prompt = "Generate a formal, academic summary (20-30 words) for a student progress report.
                Data: Average Score: $avgScore%, Quizzes: $quizCount.
                Strengths: $strengthStr. Weaknesses: $weaknessStr.
                Use an analytical, objective tone. Focus on achievement and areas for improvement.";
            }

            $summary = $this->aiService->generateRawContent($prompt, $lang);

            // Save to Database (Compliance with Section 2.6.3.11)
            $report = ProgressReport::create([
                'user_id' => $userId,
                'total_quizzes' => $stats['quiz_count'],
                'average_score' => $stats['avg_score'],
                'topics_studied' => $allTopics,
                'strengths' => $strengths,
                'weaknesses' => $weaknesses,
                'summary' => $summary, // Save AI Summary
                'generated_at' => now()
            ]);

            $msg = str_starts_with($lang, 'ar') ? 'تم إنشاء تقرير التقدم بنجاح' : 'Progress report generated successfully';

            return response()->json([
                'message' => $msg,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
