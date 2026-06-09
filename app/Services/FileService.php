<?php

namespace App\Services;

use App\Helpers\Helper;
use Aws\S3\S3Client;
use Exception;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class FileService
{
    public function uploadFile($request)
    {
        try {
            $file = $request->file('file');
            if ($file) {
                if (!file_exists(storage_path('app/public/files'))) {
                    mkdir(storage_path('app/public/files'), 0777, true);
                }
                $file = $file->store('files', 'public');
                Storage::disk('s3')->put($file, Storage::disk('public')->get($file));
                $fileUrl = Helper::getCloudFront($file);
                return response()->json([
                    's3_file_url' => $fileUrl,
                    'file_url' => $file,
                    'success' => true
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Upload file lỗi"
                ]);
            }
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => "Upload file lỗi"
            ]);
        }
    }

    public function getPresignedUrl($file, $type)
    {
        $file = 'files/' . Str::uuid() . '_' . $file;

        $s3 = new S3Client([
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret')
            ]
        ]);

        $cmd = $s3->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $file,
            'ContentType' => $type
        ]);

        $fileUrl = (string) $s3->createPresignedRequest($cmd, '+50 minutes')->getUri();

        return response()->json([
            's3_file_upload_url' => $fileUrl,
            's3_file_url' => Helper::getCloudFront($file),
            'file_url' => $file,
            'success' => true
        ]);
    }

    public function uploadExcelFile($request)
    {
        try {
            $data = Excel::toArray([], $request->file('file'));

            return response()->json([
                'success' => $data
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => "Upload file lỗi"
            ]);
        }
    }

    //Sử dụng cho upload file bình thường
    public function handleUploadFile($files, $path)
    {
        try {
            if (is_array($files)) {
                $outputFiles = [];
                foreach ($files as $file) {
                    $outputFiles[] = $this->__putFile($file, $path);
                }
                return $outputFiles;
            }else {
                return $this->__putFile($files, $path);
            }
        } catch (Exception $ex) {
            Helper::logException($ex);
            return false;
        }
    }

    private function __putFile($file, $path)
    {
        try {
            if ($file) {
                $extension = $file->getClientOriginalExtension();
                $path .= uniqid() . '.' . $extension;
                Storage::disk(config('filesystems.storage_disk'))->put($path, file_get_contents($file));
                return $path;
            }
            return '';
        } catch (Exception $ex) {
            Helper::logException($ex);
            return '';
        }
    }
}
