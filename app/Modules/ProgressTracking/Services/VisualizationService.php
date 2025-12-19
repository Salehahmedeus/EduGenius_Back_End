<?php

namespace App\Modules\ProgressTracking\Services;

use App\Modules\ProgressTracking\Repositories\AnalyticsRepository;

class VisualizationService
{
    public function __construct(private AnalyticsRepository $analyticsRepository)
    {
    }
}
