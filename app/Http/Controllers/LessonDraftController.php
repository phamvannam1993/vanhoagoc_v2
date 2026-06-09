<?php

namespace App\Http\Controllers;

use App\Models\LessonDraft;
use App\Models\Practice;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LessonDraftController extends Controller
{
    public function history(Request $request)
    {
        $practiceId = $request->get('practice_id');

        if (!$practiceId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu practice_id'
            ], 400);
        }

        $drafts = LessonDraft::byPractice($practiceId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($draft) {
                return [
                    'id' => $draft->id,
                    'type' => $draft->type,
                    'status' => $draft->status,
                    'has_text' => !empty($draft->lesson_doc),
                    'has_audio' => !empty($draft->lesson_noi),
                    'has_video' => !empty($draft->lesson_video),
                    'text_length' => !empty($draft->lesson_doc) ? strlen($draft->lesson_doc) : 0,
                    'created_at' => $draft->created_at->format('d/m/Y H:i'),
                    'approved_at' => $draft->approved_at?->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $drafts,
        ]);
    }

    public function preview(Request $request)
    {
        $draftId = $request->get('draft_id');

        if (!$draftId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu draft_id'
            ], 400);
        }

        $draft = LessonDraft::find($draftId);
        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy draft'
            ], 404);
        }

        // Convert S3 keys to URLs
        $audioUrl = null;
        if ($draft->lesson_noi) {
            if (Str::startsWith($draft->lesson_noi, 'http')) {
                $audioUrl = $draft->lesson_noi;
            } else {
                $audioUrl = Helper::getCloudFront($draft->lesson_noi);
            }
        }

        $videoUrl = null;
        if ($draft->lesson_video) {
            if (Str::startsWith($draft->lesson_video, 'http')) {
                $videoUrl = $draft->lesson_video;
            } else {
                $videoUrl = Helper::getCloudFront($draft->lesson_video);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $draft->id,
                'type' => $draft->type,
                'status' => $draft->status,
                'lesson_doc' => $draft->lesson_doc,
                'lesson_noi' => $audioUrl,
                'lesson_video' => $videoUrl,
                'created_at' => $draft->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    public function approve(Request $request)
    {
        $draftId = $request->get('draft_id');

        if (!$draftId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu draft_id'
            ], 400);
        }

        $draft = LessonDraft::find($draftId);
        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy draft'
            ], 404);
        }

        try {
            $practice = Practice::find($draft->practice_id);
            if (!$practice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy bài giảng'
                ], 404);
            }

            // Update practice with draft content
            if (!empty($draft->lesson_doc)) {
                $practice->lesson_doc = $draft->lesson_doc;
            }
            if (!empty($draft->lesson_noi)) {
                $practice->lesson_noi = $draft->lesson_noi;
            }
            if (!empty($draft->lesson_video)) {
                $practice->lesson_video = $draft->lesson_video;
            }
            $practice->save();

            // Mark draft as approved
            $draft->status = 'approved';
            $draft->approved_at = now();
            $draft->save();

            Log::info('Lesson draft approved', [
                'draft_id' => $draftId,
                'practice_id' => $draft->practice_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã phê duyệt bài giảng'
            ]);
        } catch (\Exception $e) {
            Log::error('Approve draft error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $draftId = $request->get('draft_id');

        if (!$draftId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu draft_id'
            ], 400);
        }

        $draft = LessonDraft::find($draftId);
        if (!$draft) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy draft'
            ], 404);
        }

        try {
            $draft->delete();

            Log::info('Lesson draft deleted', [
                'draft_id' => $draftId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa bản nháp'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete draft error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}
