<?php

namespace App\Modules\ProgressTracking\Controllers;

use App\Modules\ProgressTracking\Repositories\AnalyticsRepository;
use App\Http\Controllers\Controller;
use App\Modules\ProgressTracking\Services\VisualizationService;
use App\Modules\ProgressTracking\Services\DashboardService; // (The one we made for Home Screen)
use Illuminate\Http\Request;
use App\Modules\ProgressTracking\Models\ProgressReport;

class AnalyticsController extends Controller
{
    protected $vizService;
    protected $homeService;
    protected $repo;

    public function __construct(
        VisualizationService $vizService,
        DashboardService $homeService,
        AnalyticsRepository $repo
    ) {
        $this->vizService = $vizService;
        $this->homeService = $homeService;
        $this->repo = $repo;
    }

    /**
     * Endpoint: GET /api/dashboard/home
     * Use: For the Main Home Screen (Quick summary)
     */
    public function home()
    {
        $data = $this->homeService->getHomeData(auth('api')->user());
        return response()->json($data);
    }

    /**
     * Endpoint: GET /api/dashboard/stats
     * Use: For the "Statistics" Tab (Detailed charts)
     */
    public function stats()
    {
        $data = $this->vizService->getFullDashboard(auth('api')->id());
        return response()->json($data);
    }

    /**
     * Endpoint: POST /api/dashboard/report
     * Generates a static snapshot of current progress and saves it.
     */
    public function generateReport()
    {
        $userId = auth('api')->id();
        $stats = $this->repo->getSummaryStats($userId);
        $topics = $this->repo->getTopicPerformance($userId);

        // Identify Strengths/Weaknesses logic
        $strengths = $topics->where('avg_score', '>=', 80)->pluck('topic')->toArray();
        $weaknesses = $topics->where('avg_score', '<', 60)->pluck('topic')->toArray();
        $allTopics = $topics->pluck('topic')->toArray();

        // Save to Database (Section 2.6.3.11 Compliance)
        $report = ProgressReport::create([
            'user_id' => $userId,
            'total_quizzes' => $stats['total_quizzes'],
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
    }
}
