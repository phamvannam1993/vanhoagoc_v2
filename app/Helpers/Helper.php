<?php

namespace App\Helpers;

use Aws\S3\S3Client;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Helper
{
    /**
     * Custom format response for CMS API
     */
    public static function customResponseDataForGetListApi($data, $options = [], $arr = null)
    {
        $responseData = [
            'data' => $data->items(),
            'current_page' => $data->currentPage(),
            'total_page' => $data->lastPage(),
            'total' => $data->total(),
        ];

        return $responseData;
    }

    public static function preSignedS3Url(?string $key)
    {
        if (empty($key)) {
            return '';
        }

        if (Str::startsWith($key, 'http') || Str::contains($key, 'http:') || Str::contains($key, 'https:')) {
            return $key;
        }

        try {
            $isPublic = config('common.aws.aws_is_public_bucket');

            if (empty($isPublic)) {
                $s3Client = new S3Client([
                    'region' => env('AWS_DEFAULT_REGION'),
                    'version' => 'latest',
                ]);

                $cmd = $s3Client->getCommand('GetObject', [
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => $key,
                ]);
                $presignedUrlExpiration = '+7 days';

                $request = $s3Client->createPresignedRequest($cmd, $presignedUrlExpiration);

                $url = (string) $request->getUri();
            } else {
                $url = "https://" . config('common.aws.aws_bucket') . ".s3."  . config('common.aws.aws_region') . ".amazonaws.com/" . $key;
            }

            return $url;
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $key;
    }

    public static function getCloudFront($key)
    {
        if (empty($key)) {
            return '';
        }

        if (Str::startsWith($key, 'http') || Str::contains($key, 'http:') || Str::contains($key, 'https:')) {
            return $key;
        }

        return config('common.aws.cloud_front_domain') . '/' . trim($key, '/');
    }

    public static function logException(\Exception $e)
    {
        Log::error('Exception caught', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    public static function logInfo(string $message, $context = []): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? null;
        $file = $backtrace['file'] ?? 'unknown file';
        $line = $backtrace['line'] ?? 'unknown line';

        Log::info("[$file:$line] $message", $context);
    }
}
