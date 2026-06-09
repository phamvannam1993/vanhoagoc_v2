<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\QuizGeneratorService;
use Inertia\Inertia;
use Aws\S3\S3Client;

class LessonQuizGeneratorController extends Controller
{
    public function show(Request $request)
    {
        $appId = $request->query('app_id');
        $bookId = $request->query('book_id');
        $weekId = $request->query('week_id');
        $practiceId = $request->query('practice_id');

        return Inertia::render('Lessons/GenerateQuiz', [
            'app_id' => $appId,
            'book_id' => $bookId,
            'week_id' => $weekId,
            'practice_id' => $practiceId,
        ]);
    }

    public function uploadMultiFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:15360'
            ]);

            $file = $request->file('file');
            $url = $this->uploadFileToS3($file);

            if (!$url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi upload file.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'url' => $url,
                'file_name' => $file->getClientOriginalName()
            ]);
        } catch (\Exception $e) {
            Log::error('Upload multi file failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveQuiz(Request $request, QuizGeneratorService $quizGeneratorService)
    {
        $validated = $request->validate([
            'quiz_data' => 'required|array',
            'practice_id' => 'required|integer',
            'week_id' => 'nullable|integer',
            'book_id' => 'nullable|integer',
        ]);

        $result = $quizGeneratorService->saveQuiz(
            $validated['quiz_data'],
            [
                'practice_id' => $validated['practice_id'],
                'week_id' => $validated['week_id'],
                'book_id' => $validated['book_id'],
            ]
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'input_type' => 'required|in:text,file,image',
            'content_text' => 'nullable|string|max:10000',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'content_urls' => 'nullable|json',
            'image_base64' => 'nullable|string',
            'muc_do' => 'required|string',
            'lop' => 'required|string',
            'mon' => 'required|string',
            'counts' => 'required|array',
        ]);

        // Calculate so_cau from counts
        $soCau = ($validated['counts']['chon'] ?? 0) +
                 ($validated['counts']['sx'] ?? 0) +
                 ($validated['counts']['noi'] ?? 0);

        if ($soCau < 5 || $soCau > 50) {
            return response()->json([
                'success' => false,
                'message' => 'Tổng số câu phải từ 5 đến 50.'
            ], 422);
        }

        $contentUrl = '';
        $contentUrls = [];
        $contentText = '';

        if ($validated['input_type'] === 'text') {
            if (empty($validated['content_text'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập nội dung câu hỏi.'
                ], 422);
            }
            $contentText = $validated['content_text'];
        } elseif ($validated['input_type'] === 'file') {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn file.'
                ], 422);
            }
            $file = $request->file('file');
            $contentUrl = $this->uploadFileToS3($file);
            if (!$contentUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi upload file. Vui lòng thử lại.'
                ], 500);
            }
        } elseif ($validated['input_type'] === 'image') {
            // Handle multiple file URLs
            if (!empty($validated['content_urls'])) {
                $contentUrls = json_decode($validated['content_urls'], true);
                if (!is_array($contentUrls) || empty($contentUrls)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vui lòng upload ảnh.'
                    ], 422);
                }
            } elseif (!empty($validated['image_base64'])) {
                // Fallback: single base64 image
                $contentUrl = $this->uploadBase64ImageToS3($validated['image_base64']);
                if (!$contentUrl) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh. Vui lòng thử lại.'
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng upload ảnh.'
                ], 422);
            }
        }

        $result = $this->callQuizApi(
            $contentText,
            $contentUrl,
            empty($contentUrls) ? [] : $contentUrls,
            $validated['muc_do'],
            $soCau,
            $validated['lop'],
            $validated['mon'],
            $validated['counts']
        );

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo câu hỏi thành công.',
            'data' => $result['data']
        ], 200);
    }

    private function uploadFileToS3($file): ?string
    {
        try {
            $path = 'quiz-generator/' . time() . '_' . $file->getClientOriginalName();
            $disk = Storage::disk('s3');
            $disk->put($path, file_get_contents($file));

            // Generate presigned URL using AWS SDK
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ]
            ]);

            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $path
            ]);

            $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
            $url = (string)$request->getUri();

            Log::info('File uploaded to S3', [
                'path' => $path,
                'url' => $url,
                'file_name' => $file->getClientOriginalName()
            ]);
            return $url;
        } catch (\Exception $e) {
            Log::error('Upload file to S3 failed: ' . $e->getMessage());
            return null;
        }
    }

    private function uploadBase64ImageToS3(string $base64String): ?string
    {
        try {
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $base64String);
            $decodedImage = base64_decode($imageData, true);

            if ($decodedImage === false) {
                return null;
            }

            $path = 'quiz-generator/' . time() . '_image.png';
            $disk = Storage::disk('s3');
            $disk->put($path, $decodedImage, 'public');

            $url = $disk->url($path);
            return $url;
        } catch (\Exception $e) {
            Log::error('Upload base64 image to S3 failed: ' . $e->getMessage());
            return null;
        }
    }

    private function callQuizApi(string $contentText, string $contentUrl, array $contentUrls, string $mucDo, int $soCau, string $lop, string $mon, array $counts): array
    {
        try {
            $apiKey = env('QUIZ_API_KEY');
            $apiUrl = env('QUIZ_API_URL');

            if (!$apiKey || !$apiUrl) {
                return [
                    'success' => false,
                    'message' => 'Cấu hình API không đầy đủ.'
                ];
            }

            $payload = [
                'muc_do' => $mucDo,
                'so_cau' => $soCau,
                'lop' => $lop,
                'mon' => $mon,
                'counts' => $counts,
                'format' => 'json',
                'kinds' => array_keys($counts)
            ];

            // Gửi tất cả content có sẵn, API sẽ xử lý theo ưu tiên
            if (!empty($contentText)) {
                $payload['content_text'] = $contentText;
            }
            if (!empty($contentUrls)) {
                $payload['content_urls'] = $contentUrls;
            }
            if (!empty($contentUrl)) {
                $payload['content_url'] = $contentUrl;
            }
 
            $response = Http::withHeaders([
                'X-Quiz-Api-Key' => $apiKey,
            ])->timeout(60)->post($apiUrl, $payload);

            Log::info('Quiz API Response:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                $errorMsg = $response->body() ?: 'Lỗi từ API: ' . $response->status();
                return [
                    'success' => false,
                    'message' => $errorMsg
                ];
            }

            $result = $response->json();

            if (empty($result)) {
                return [
                    'success' => false,
                    'message' => 'API trả về kết quả rỗng.'
                ];
            }

            return [
                'success' => true,
                'data' => $result
            ];
        } catch (\Exception $e) {
            Log::error('Call quiz API failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi gọi API: ' . $e->getMessage()
            ];
        }
    }
}
