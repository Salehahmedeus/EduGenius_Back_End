<?php

namespace App\Modules\AILearning\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AILearning\Services\ResponseSynthesizer;
use Illuminate\Http\Request;

class AIController extends Controller
{
    protected $aiService;

    public function __construct(ResponseSynthesizer $aiService)
    {
        $this->aiService = $aiService;
    }

    public function ask(Request $request)
    {
        $request->validate(['query' => 'required|string|min:2']);

        $result = $this->aiService->generate(
            auth('api')->id(),
            $request->input('query')
        );

        return response()->json($result);
    }

    public function history()
    {
        $history = $this->aiService->getChatHistory(auth('api')->id());
        return response()->json($history);
    }
}
