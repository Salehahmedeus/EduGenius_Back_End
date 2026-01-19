<?php

namespace App\Modules\ProgressTracking\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ProgressTracking\Services\VisualizationService;
use App\Modules\ProgressTracking\Services\DashboardService; // (The one we made for Home Screen)
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
}
