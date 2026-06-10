<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Practice;
use App\Models\LessonDraft;

class LessonGeneratorController extends Controller
{
    private $apiUrl = 'https://st-f9a9538d69944a6eb7103f62949f8a08.ecs.ap-southeast-1.on.aws/api/lesson';
    private $apiKey = 'GS6AyLMiRyo6roZXHI7fYZLXxTPqxsnqcWF9wQ24Pic';

    public function generateText(Request $request)
    {
        try {
            $validated = $request->validate([
                'content_text' => 'nullable|string',
                'content_urls' => 'nullable|array',
                'lop' => 'required|string',
                'mon' => 'required|string',
                'app_id' => 'nullable|integer',
                'book_id' => 'nullable|integer',
                'week_id' => 'nullable|integer',
                'practice_id' => 'nullable|integer',
            ]);

            if (empty($validated['content_text']) && empty($validated['content_urls'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập nội dung hoặc chọn file.'
                ], 422);
            }
            
            $payload = [
                'lop' => $validated['lop'],
                'mon' => $validated['mon'],
            ];

            if (!empty($validated['content_text'])) {
                $payload['content_text'] = $validated['content_text'];
            }
            if (!empty($validated['content_urls'])) {
                $payload['content_urls'] = $validated['content_urls'];
            }

            $response = Http::withHeaders([
                'X-Lesson-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post($this->apiUrl . '/text', $payload);

            if (!$response->successful()) {
                Log::error('Text generation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status()
                ], 500);
            }

            $result = $response->json();

            // Save to draft if practice_id is provided
            $saved = false;
            if (!empty($validated['practice_id']) && !empty($result['text_html'])) {
                $saved = $this->saveLessonDraft(
                    $validated['practice_id'],
                    $validated['app_id'] ?? null,
                    $validated['book_id'] ?? null,
                    $validated['week_id'] ?? null,
                    $result['text_html'],
                    null,
                    null,
                    'text'
                );
            }

            return response()->json([
                'success' => true,
                'result' => $result,
                'auto_saved' => $saved,
                'message' => $saved ? 'Bài giảng đã lưu vào nháp' : 'Bài giảng được tạo thành công',
            ]);
        } catch (\Exception $e) {
            Log::error('Generate text error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateAudio(Request $request)
    {
        try {
            $validated = $request->validate([
                'content_text' => 'nullable|string',
                'content_urls' => 'nullable|array',
                'lop' => 'required|string',
                'mon' => 'required|string',
                'voice' => 'required|in:alloy,echo,fable,onyx,nova,shimmer',
                'app_id' => 'nullable|integer',
                'book_id' => 'nullable|integer',
                'week_id' => 'nullable|integer',
                'practice_id' => 'nullable|integer',
            ]);

            if (empty($validated['content_text']) && empty($validated['content_urls'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập nội dung hoặc chọn file.'
                ], 422);
            }

            $payload = [
                'lop' => $validated['lop'],
                'mon' => $validated['mon'],
                'voice' => $validated['voice'],
            ];

            if (!empty($validated['content_text'])) {
                $payload['content_text'] = $validated['content_text'];
            }
            if (!empty($validated['content_urls'])) {
                $payload['content_urls'] = $validated['content_urls'];
            }

            $response = Http::withHeaders([
                'X-Lesson-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post($this->apiUrl . '/audio', $payload);

            if (!$response->successful()) {
                Log::error('Audio generation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi từ API: ' . $response->status()
                ], 500);
            }

            $result = $response->json();

            // Cache task info for polling
            if (isset($result['id'])) {
                Cache::put("lesson_task_{$result['id']}", $result, 3600); // 1 hour
            }

            return response()->json([
                'success' => true,
                'task_id' => $result['id'] ?? null,
                'status' => $result['status'] ?? 'pending',
            ]);
        } catch (\Exception $e) {
            Log::error('Generate audio error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBundleStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'bundle_id' => 'required|string',
            ]);

            $bundleId = $validated['bundle_id'];

            $response = Http::withHeaders([
                'X-Lesson-Api-Key' => $this->apiKey,
            ])
            ->timeout(30)
            ->get($this->apiUrl . '/bundles/' . $bundleId);

            if (!$response->successful()) {
                Log::error('Bundle status check failed', [
                    'bundle_id' => $bundleId,
                    'status' => $response->status(),
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể kiểm tra trạng thái bundle',
                ], 500);
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('Bundle status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function taskStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'task_id' => 'required|string',
            ]);

            $taskId = $validated['task_id'];

            $response = Http::withHeaders([
                'X-Lesson-Api-Key' => $this->apiKey,
            ])
            ->timeout(30)
            ->get($this->apiUrl . '/tasks/' . $taskId);

            if (!$response->successful()) {
                Log::error('Task status check failed', [
                    'task_id' => $taskId,
                    'status' => $response->status(),
                ]);
                return response()->json([
                    'status' => 'error',
                    'error_message' => 'Không thể kiểm tra trạng thái task',
                ], 500);
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('Task status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'error_message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateBundle(Request $request)
    {
        try {
            $validated = $request->validate([
                'content_text' => 'nullable|string',
                'content_urls' => 'nullable|array',
                'lop' => 'required|string',
                'mon' => 'required|string',
                'voice' => 'required|string',
                'types' => 'required|array',
                'level_mix' => 'nullable|array',
            ]);

            if (empty($validated['content_text']) && empty($validated['content_urls'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập nội dung hoặc chọn file.',
                ], 422);
            }

            $payload = [
                'content_text' => $validated['content_text'],
                'content_urls' => $validated['content_urls'] ?? [],
                'lop' => $validated['lop'],
                'mon' => $validated['mon'],
                'voice' => $validated['voice'],
                'types' => $validated['types'],
            ];

            if (!empty($validated['level_mix'])) {
                $payload['level_mix'] = $validated['level_mix'];
            }

            // $response = Http::withHeaders([
            //     'X-Lesson-Api-Key' => $this->apiKey,
            //     'Content-Type' => 'application/json',
            // ])
            // ->timeout(60)
            // ->post($this->apiUrl . '/bundle', $payload);

            // if (!$response->successful()) {
            //     Log::error('Bundle generation failed', [
            //         'status' => $response->status(),
            //         'body' => $response->body(),
            //     ]);
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Lỗi từ API: ' . $response->status()
            //     ], 500);
            // }
            return response()->json(['bundle_id' => 27]);
            // return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('Generate bundle error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveBundleResult(Request $request)
    {
        try {
            $validated = $request->validate([
                'practice_id' => 'required|integer',
                'bundle_id' => 'required|string',
                'app_id' => 'nullable|integer',
                'book_id' => 'nullable|integer',
                'week_id' => 'nullable|integer',
            ]);

            $practice = Practice::find($validated['practice_id']);
            if (!$practice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bài giảng',
                ]);
            }

            // Fetch bundle data from API
            $response = Http::withHeaders([
                'X-Lesson-Api-Key' => $this->apiKey,
            ])
            ->timeout(30)
            ->get($this->apiUrl . '/bundles/' . $validated['bundle_id']);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể lấy dữ liệu bundle',
                ], 500);
            }

            $bundle = $response->json();
            $children = $bundle['children'] ?? [];

            // Process each child
            foreach ($children as $type => $child) {
                if ($child['status'] !== 'done') {
                    continue;
                }

                switch ($type) {
                    case 'baidoc':
                        if (!empty($child['result_text'])) {
                            $this->saveLessonDraft(
                                $validated['practice_id'],
                                $validated['app_id'] ?? null,
                                $validated['book_id'] ?? null,
                                $validated['week_id'] ?? null,
                                $child['result_text'],
                                null,
                                null,
                                'text'
                            );
                        }
                        break;

                    case 'sachnoi':
                        if (!empty($child['result_url'])) {
                            $s3Key = $this->saveLessonAudio($validated['practice_id'], $child['result_url']);
                            if ($s3Key) {
                                $this->saveLessonDraft(
                                    $validated['practice_id'],
                                    $validated['app_id'] ?? null,
                                    $validated['book_id'] ?? null,
                                    $validated['week_id'] ?? null,
                                    null,
                                    $s3Key,
                                    null,
                                    'audio'
                                );
                            }
                        }
                        break;

                    case 'video':
                        if (!empty($child['result_url'])) {
                            $s3Key = $this->saveLessonVideo($validated['practice_id'], $child['result_url']);
                            if ($s3Key) {
                                $this->saveLessonDraft(
                                    $validated['practice_id'],
                                    $validated['app_id'] ?? null,
                                    $validated['book_id'] ?? null,
                                    $validated['week_id'] ?? null,
                                    null,
                                    null,
                                    $s3Key,
                                    'video'
                                );
                            }
                        }
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Dữ liệu bundle đã lưu vào nháp',
            ]);
        } catch (\Exception $e) {
            Log::error('Save bundle result error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveAudioResult(Request $request)
    {
        try {
            $validated = $request->validate([
                'practice_id' => 'required|integer',
                'result_url' => 'required|string',
                'app_id' => 'nullable|integer',
                'book_id' => 'nullable|integer',
                'week_id' => 'nullable|integer',
            ]);

            $practice = Practice::find($validated['practice_id']);
            if (!$practice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bài giảng',
                ]);
            }

            // Download audio from presigned URL and upload to S3
            $s3Key = $this->saveLessonAudio($validated['practice_id'], $validated['result_url']);

            if (!$s3Key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi lưu tệp âm thanh',
                ], 500);
            }

            // Save to draft
            $this->saveLessonDraft(
                $validated['practice_id'],
                $validated['app_id'] ?? null,
                $validated['book_id'] ?? null,
                $validated['week_id'] ?? null,
                null,
                $s3Key,
                null,
                'audio'
            );

            return response()->json([
                'success' => true,
                'message' => 'Sách nói đã lưu vào nháp',
                'audio_url' => $s3Key,
            ]);
        } catch (\Exception $e) {
            Log::error('Save audio result error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    private function saveLessonContent(int $practiceId, array $result): bool
    {
        try {
            $practice = Practice::find($practiceId);
            if (!$practice) {
                Log::warning('Practice not found', ['practice_id' => $practiceId]);
                return false;
            }

            // Save generated text content to lesson_doc field
            if (!empty($result['text_html'])) {
                $practice->lesson_doc = $result['text_html'];
                $practice->save();
                Log::info('Lesson content saved', [
                    'practice_id' => $practiceId,
                    'content_length' => strlen($result['text_html'])
                ]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error saving lesson content: ' . $e->getMessage(), [
                'practice_id' => $practiceId
            ]);
            return false;
        }
    }

    private function saveLessonAudio(int $practiceId, string $audioUrl): ?string
    {
        try {
            $practice = Practice::find($practiceId);

            if (!$practice) {
                Log::warning('Practice not found for audio', [
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            Log::info('Downloading audio from URL', [
                'practice_id' => $practiceId,
                'audio_url' => $audioUrl
            ]);

            // Download audio file from presigned URL
            $response = Http::timeout(120)->get($audioUrl);

            if (!$response->successful()) {
                Log::error('Failed to download audio', [
                    'status' => $response->status(),
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            $audioContent = $response->body();

            if (empty($audioContent)) {
                Log::error('Downloaded audio is empty', [
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            Log::info('Audio downloaded', [
                'size' => strlen($audioContent),
                'practice_id' => $practiceId
            ]);

            // Generate S3 key
            $timestamp = now()->format('Y/m/d');
            $filename = uniqid('audio_', true) . '.mp3';
            $s3Key = "lessons/audio/{$timestamp}/{$filename}";

            Log::info('Uploading audio to S3', [
                's3_key' => $s3Key
            ]);

            // Upload to S3
            $uploaded = Storage::disk('s3')->put($s3Key, $audioContent, [
                'ContentType' => 'audio/mpeg'
            ]);

            if (!$uploaded) {
                Log::error('Failed to upload audio to S3', [
                    's3_key' => $s3Key,
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            // Verify file exists
            if (!Storage::disk('s3')->exists($s3Key)) {
                Log::error('Audio file not found after upload', [
                    's3_key' => $s3Key,
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            Log::info('Audio uploaded to S3 successfully', [
                'practice_id' => $practiceId,
                's3_key' => $s3Key,
            ]);

            return $s3Key;

        } catch (\Exception $e) {
            Log::error('Error saving audio lesson: ' . $e->getMessage(), [
                'practice_id' => $practiceId,
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    private function saveLessonVideo(int $practiceId, string $videoUrl): ?string
    {
        try {
            $practice = Practice::find($practiceId);

            if (!$practice) {
                Log::warning('Practice not found for video', [
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            Log::info('Downloading video from URL', [
                'practice_id' => $practiceId,
                'video_url' => $videoUrl
            ]);

            // Download video file from presigned URL
            $response = Http::timeout(300)->get($videoUrl);

            if (!$response->successful()) {
                Log::error('Failed to download video', [
                    'status' => $response->status(),
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            $videoContent = $response->body();

            if (empty($videoContent)) {
                Log::error('Downloaded video is empty', [
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            Log::info('Video downloaded', [
                'size' => strlen($videoContent),
                'practice_id' => $practiceId
            ]);

            // Generate S3 key
            $timestamp = now()->format('Y/m/d');
            $filename = uniqid('video_', true) . '.mp4';
            $s3Key = "lessons/video/{$timestamp}/{$filename}";

            Log::info('Uploading video to S3', [
                's3_key' => $s3Key
            ]);

            // Upload to S3
            $uploaded = Storage::disk('s3')->put($s3Key, $videoContent, [
                'ContentType' => 'video/mp4'
            ]);

            if (!$uploaded) {
                Log::error('Failed to upload video to S3', [
                    's3_key' => $s3Key,
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            // Verify file exists
            if (!Storage::disk('s3')->exists($s3Key)) {
                Log::error('Video file not found after upload', [
                    's3_key' => $s3Key,
                    'practice_id' => $practiceId
                ]);
                return null;
            }

            Log::info('Video uploaded to S3 successfully', [
                'practice_id' => $practiceId,
                's3_key' => $s3Key,
            ]);

            return $s3Key;

        } catch (\Exception $e) {
            Log::error('Error saving video lesson: ' . $e->getMessage(), [
                'practice_id' => $practiceId,
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    private function saveLessonDraft(
        int $practiceId,
        ?int $appId,
        ?int $bookId,
        ?int $weekId,
        ?string $lessonDoc,
        ?string $lessonNoi,
        ?string $lessonVideo,
        string $type
    ): bool {
        try {
            LessonDraft::create([
                'practice_id' => $practiceId,
                'app_id' => $appId,
                'book_id' => $bookId,
                'week_id' => $weekId,
                'lesson_doc' => $lessonDoc,
                'lesson_noi' => $lessonNoi,
                'lesson_video' => $lessonVideo,
                'type' => $type,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            Log::info('Lesson draft saved', [
                'practice_id' => $practiceId,
                'type' => $type,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error saving lesson draft: ' . $e->getMessage(), [
                'practice_id' => $practiceId,
            ]);
            return false;
        }
    }

    public function getStudentsByClass(Request $request)
    {
        try {
            $classCode = $request->get('class_code');

            // TODO: Fetch từ database dựa vào class_code
            // Giả định structure: StudentClass model + Student model
            // Format response: [{ id, name, grp (yeu/kha/gioi) }, ...]

            // Tạm dùng mock data từ frontend
            return response()->json([
                'success' => true,
                'students' => [],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy dữ liệu học sinh',
            ], 500);
        }
    }

    public function assignExercises(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_ids' => 'required|array',
                'class_code' => 'required|string',
                'due_date' => 'required|date',
                'due_time' => 'required|date_format:H:i',
                'note' => 'nullable|string',
                'exercise_items' => 'required|array',
            ]);

            $exerciseItems = $validated['exercise_items'];
            $studentIds = $validated['student_ids'];
            $classCode = $validated['class_code'];

            foreach ($exerciseItems as $item) {
                $assignment = \App\Models\ExerciseAssignment::create([
                    'exercise_item_id' => $item['id'],
                    'class_code' => $classCode,
                    'due_date' => $validated['due_date'],
                    'due_time' => $validated['due_time'],
                    'note' => $validated['note'],
                ]);

                foreach ($studentIds as $studentId) {
                    \App\Models\AssignmentStudent::create([
                        'exercise_assignment_id' => $assignment->id,
                        'student_id' => $studentId,
                        'status' => 'pending',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Giao bài thành công',
            ]);
        } catch (\Exception $e) {
            Log::error('Error assigning exercises: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi giao bài: ' . $e->getMessage(),
            ], 500);
        }
    }
}
