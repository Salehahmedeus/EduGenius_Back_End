<?php

namespace App\Modules\ProgressTracking\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 👇 Import your Services and Repository
use App\Modules\ProgressTracking\Repositories\AnalyticsRepository;
use App\Modules\ProgressTracking\Services\DashboardService;
use App\Modules\ProgressTracking\Services\VisualizationService;
use App\Modules\ProgressTracking\Models\ProgressReport;

class AnalyticsController extends Controller
{
    protected $repo;
    protected $dashboardService;
    protected $vizService;

    /**
     * Dependency Injection:
     * We inject the Repository (for raw data) and both Services (for formatting).
     */
    public function __construct(
        AnalyticsRepository $repo,
        DashboardService $dashboardService,
        VisualizationService $vizService
    ) {
        $this->repo = $repo;
        $this->dashboardService = $dashboardService;
        $this->vizService = $vizService;
    }

    /**
     * Endpoint: GET /api/dashboard/home
     * Description: Returns the "Master JSON" for the Home Screen.
     */
    public function home(Request $request)
    {
        $user = auth('api')->user();
        $userId = $user->id;

        // 1. Fetch Stats ONCE (Source of Truth)
        // We get the numbers here and pass them down to avoid recalculating
        $stats = $this->repo->getUnifiedStats($userId);

        // 2. Get Basic Info (User Profile, Recents List, Recommendation)
        $basicInfo = $this->dashboardService->getBasicInfo($user, $stats);

        // 3. Get Visuals (Charts, Insights)
        $visuals = $this->vizService->getVisuals($userId, $stats);

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
    public function generateReport()
    {
        try {
            $userId = auth('api')->id();

            // Fetch raw data needed for the report
            $stats = $this->repo->getUnifiedStats($userId);
            $topicStats = $this->repo->getTopicPerformance($userId); // Need raw topic collection

            // Calculate Strengths & Weaknesses
            $strengths = $topicStats->where('avg_score', '>=', 80)->pluck('topic')->toArray();
            $weaknesses = $topicStats->where('avg_score', '<', 60)->pluck('topic')->toArray();
            $allTopics = $topicStats->pluck('topic')->toArray();

            // Save to Database (Compliance with Section 2.6.3.11)
            $report = ProgressReport::create([
                'user_id' => $userId,
                'total_quizzes' => $stats['quiz_count'],
                'average_score' => $stats['avg_score'],
                'topics_studied' => $allTopics,
                'strengths' => $strengths,
                'weaknesses' => $weaknesses,
                'generated_at' => now()
            ]);

            return response()->json([
                'message' => 'Progress report generated successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
