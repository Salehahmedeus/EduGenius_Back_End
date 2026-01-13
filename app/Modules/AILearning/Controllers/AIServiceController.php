<?php

namespace App\Modules\AILearning\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AILearning\Services\ResponseSynthesizer;
use Illuminate\Http\Request;
use App\Modules\ContentManagement\Services\FileProcessor;
use App\Modules\ContentManagement\Services\FileStorageService;
use App\Modules\AILearning\Models\ConversationContext;

class AIServiceController extends Controller
{
    protected $aiService;
    protected $fileProcessor;
    protected $storageService;

    public function __construct(
        ResponseSynthesizer $aiService,
        FileProcessor $fileProcessor,
        FileStorageService $storageService
    ) {
        $this->aiService = $aiService;
        $this->fileProcessor = $fileProcessor;
        $this->storageService = $storageService;
    }

    public function ask(Request $request)
    {
        $$request->validate([
            'query' => 'required|string|min:2',
            'conversation_id' => 'nullable|integer|exists:conversation_contexts,id'
        ]);

        $result = $this->aiService->generate(
            auth('api')->id(),
            $request->input('query'),
            $request->input('conversation_id') // Can be null (New Chat) or ID (Continue)
        );

        return response()->json($result);
    }

    public function listChats()
    {
        return response()->json(ConversationContext::where('user_id', auth('api')->id())->latest()->get());
    }

    public function history()
    {
        $history = $this->aiService->getChatHistory(auth('api')->id());
        return response()->json($history);
    }
    public function askWithFile(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'file'  => 'required|file|mimes:pdf,docx,txt|max:10240'
        ]);

        try {
            $user = auth('api')->user();
            $file = $request->file('file');

            // 1. Temporarily store file to extract text
            // (We store it so pdfparser can read it from disk)
            $path = $this->storageService->store($file, $user->id);
            $fullPath = $this->storageService->getAbsolutePath($path);

            // 2. Extract Text
            $text = $this->fileProcessor->extractText($fullPath, $file->getMimeType());

            // 3. Ask AI using THAT text
            $result = $this->aiService->generateFromSpecificText(
                $user->id,
                $request->input('query'),
                $text
            );

            // 4. Cleanup (Optional: Delete file if you don't want to save it)
            // If you WANT to save it to their library, call the ContentService here.
            // For now, let's assume it's temporary:
            $this->storageService->delete($path);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
