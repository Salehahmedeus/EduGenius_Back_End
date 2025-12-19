<?php

namespace App\Modules\AILearning\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\ApiResponseTrait;
use App\Modules\AILearning\Services\ResponseSynthesizer;
use Illuminate\Http\Request;

class AIServiceController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private ResponseSynthesizer $responseSynthesizer)
    {
    }

    public function query(Request $request)
    {
    }
}
