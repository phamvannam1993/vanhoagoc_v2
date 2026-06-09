<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use Illuminate\Http\Request;
use App\Services\FileService;

class FileController extends BaseModuleController
{
    private $fileService;

    public function __construct(
        FileService $fileService
    ) {
        $this->fileService = $fileService;
    }

    public function uploadFile(Request $request)
    {
        $type = $request->get('type', '');

        if ($type == 'excel') {
            return $this->fileService->uploadExcelFile($request);
        } else {
            return $this->fileService->uploadFile($request);
        }
    }

    public function uploadFilePresigned(Request $request)
    {
        $name = $request->get('filename');
        $type = $request->get('type', '');

        return $this->fileService->getPresignedUrl($name, $type);
    }
}
