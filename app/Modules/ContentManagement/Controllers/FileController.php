<?php

namespace App\Modules\ContentManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContentManagement\Services\ContentService; //  Only depends on Service
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    protected $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,docx,txt|max:10240',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 400);

        try {
            $material = $this->contentService->processUpload(
                $request->file('file'),
                auth('api')->id()
            );

            return response()->json([
                'message' => 'File uploaded successfully',
                'data' => $material
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $this->contentService->deleteMaterial($id, auth('api')->id());

            return response()->json([
                'message' => 'File deleted successfully'
            ]);
        } catch (\Exception $e) {
            // Check if it was an Authorization error or Missing File error
            $status = ($e->getMessage() === "Unauthorized access to this file") ? 403 : 404;

            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json(['error' => 'Please provide a search query (q parameter)'], 400);
        }

        $results = $this->contentService->searchUserFiles(auth('api')->id(), $query);

        return response()->json([
            'count' => count($results),
            'data' => $results
        ]);
    }
}
