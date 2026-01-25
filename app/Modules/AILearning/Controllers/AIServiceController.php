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
        // 1. Unified Validation
        // 'file' is now nullable (Optional)
        $request->validate([
            'query' => 'required|string|min:2',
            'conversation_id' => 'nullable|integer|exists:conversation_contexts,id',
            'file'  => 'nullable|file|mimes:pdf,docx,txt|max:10240'
        ]);

        $userId = auth('api')->id();
        $query = $request->input('query');
        $convId = $request->input('conversation_id');

        // Get Language from Header (Default to 'en' if missing)
        // Flutter sends 'ar' or 'ar-SA'
        $lang = $request->header('Accept-Language', 'en');

        try {
            // 2. Check: Did the user upload a file?
            if ($request->hasFile('file')) {
                // === PATH A: Chat with uploaded file ===

                $file = $request->file('file');
                $path = $this->storageService->store($file, $userId);
                $fullPath = $this->storageService->getAbsolutePath($path);

                // Extract text
                $text = $this->fileProcessor->extractText($fullPath, $file->getMimeType());

                // Generate response (Pass language)
                $result = $this->aiService->generateFromSpecificText($userId, $query, $text, $convId, $lang);

                // Cleanup temp file
                $this->storageService->delete($path);

                return response()->json($result);
            } else {
                // === PATH B: Normal Chat (Database Search) ===

                // Pass language to generate method
                $result = $this->aiService->generate($userId, $query, $convId, $lang);

                return response()->json($result);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function listChats()
    {
        return response()->json(ConversationContext::where('user_id', auth('api')->id())->latest()->get());
    }

    public function history($id)
    {
        $messages = $this->aiService->getChatMessages(auth('api')->id(), $id);
        return response()->json($messages);
    }

    public function deleteChat($id)
    {
        $success = $this->aiService->deleteChat(auth('api')->id(), $id);

        if (!$success) {
            return response()->json(['error' => 'Chat not found or unauthorized'], 404);
        }

        return response()->json(['message' => 'Chat deleted successfully']);
    }
}
