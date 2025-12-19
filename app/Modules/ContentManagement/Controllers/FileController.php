<?php

namespace App\Modules\ContentManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\ApiResponseTrait;
use App\Modules\ContentManagement\Services\FileProcessor;
use App\Modules\ContentManagement\Services\FileStorageService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private FileProcessor $fileProcessor,
        private FileStorageService $fileStorageService,
    ) {
    }

    public function upload(Request $request)
    {
    }
}
