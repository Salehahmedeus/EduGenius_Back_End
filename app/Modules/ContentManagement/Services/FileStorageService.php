<?php

namespace App\Modules\ContentManagement\Services;

use App\Modules\ContentManagement\Repositories\MaterialRepository;

class FileStorageService
{
    public function __construct(private MaterialRepository $materialRepository)
    {
    }
}
