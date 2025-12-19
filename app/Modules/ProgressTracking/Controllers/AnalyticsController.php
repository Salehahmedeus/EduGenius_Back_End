<?php

namespace App\Modules\ProgressTracking\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\ApiResponseTrait;
use App\Modules\ProgressTracking\Services\VisualizationService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private VisualizationService $visualizationService)
    {
    }

    public function index(Request $request)
    {
    }
}
