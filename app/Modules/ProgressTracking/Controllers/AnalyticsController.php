<?php

namespace App\Modules\ProgressTracking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ProgressTracking\Services\VisualizationService;
use App\Modules\ProgressTracking\Services\DashboardService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $vizService;
    protected $homeService;

    public function __construct(
        VisualizationService $vizService,
        DashboardService $homeService
    ) {
        $this->vizService = $vizService;
        $this->homeService = $homeService;
    }

    /**
     * Endpoint: GET /api/dashboard/home
     * The "Super Endpoint" that powers the entire Dashboard Screen.
     */
    public function home(Request $request)
    {
        $user = auth('api')->user();

        // 1. Get Basic Info
        $basicData = $this->homeService->getHomeData($user);

        // 2. Get Charts & Stats (Cached via Redis inside the service)
        $statsData = $this->vizService->getFullDashboard($user->id);

        // 3. Merge and Return
        return response()->json(array_merge($basicData, $statsData));
    }

    /**
     * Endpoint: POST /api/dashboard/report
     * Generates a permanent report card.
     */
    public function generateReport()
    {
        try {
            $userId = auth('api')->id();

            // Logic to create the static report snapshot
            // (We instantiate the repository directly here for the specific report logic)
            $repo = new \App\Modules\ProgressTracking\Repositories\AnalyticsRepository();
            $stats = $repo->getSummaryStats($userId);
            $topics = $repo->getTopicPerformance($userId);

            $report = \App\Modules\ProgressTracking\Models\ProgressReport::create([
                'user_id' => $userId,
                'total_quizzes' => $stats['total_quizzes'],
                'average_score' => $stats['avg_score'],
                'topics_studied' => $topics->pluck('topic')->toArray(),
                'strengths' => $topics->where('avg_score', '>=', 80)->pluck('topic')->toArray(),
                'weaknesses' => $topics->where('avg_score', '<', 60)->pluck('topic')->toArray(),
                'generated_at' => now()
            ]);

            return response()->json([
                'message' => 'Report generated successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
