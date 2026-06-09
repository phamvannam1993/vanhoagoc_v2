<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\PracticeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VoiceController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Lesson/CreateVoice', [
            'query' => $request->query()
        ]);
    }

    public function store(Request $request, PracticeService $practiceService)
    {
        $params = $request->all([
            'practice_id',
            'content'
        ]);

        $filter = [
            'id' => $params['practice_id']
        ];

        $update = [
            'lesson_noi' => $params['content'] ?? null
        ];

        $practiceService->storeByConditions($filter, $update);

        return response()->json([
            'status' => true,
        ]);
    }

    public function edit(Request $request, PracticeService $practiceService)
    {
        $id = $request->get('practice_id');
        $record  = $practiceService->getDetail($id);
        $isVideo = false;
        $voiceUrl = null;

        if ($record->lesson_noi) {
            // Determine if it's video - check the stored value
            $pathToCheck = $record->lesson_noi;
            if (Str::startsWith($record->lesson_noi, 'http')) {
                // For presigned URLs, extract path from URL
                $parsedUrl = parse_url($record->lesson_noi, PHP_URL_PATH);
                $pathToCheck = $parsedUrl ?: $record->lesson_noi;
            }

            if (Str::endsWith(strtolower($pathToCheck), '.mp4')) {
                $isVideo = true;
            }

            // Determine the audio URL to display
            if (Str::startsWith($record->lesson_noi, 'http')) {
                // It's a presigned URL from external API - use directly
                $voiceUrl = $record->lesson_noi;
            }
            elseif (Str::startsWith($record->lesson_noi, 'lessons/audio/')) {
                // It's an S3 key from our bucket - use CloudFront
                $voiceUrl = Helper::getCloudFront($record->lesson_noi);
            }
            else {
                // Otherwise assume it's an S3 key and use CloudFront
                $voiceUrl = Helper::getCloudFront($record->lesson_noi);
            }
        }

        Log::info('EditVoice loaded', [
            'practice_id' => $id,
            'lesson_noi' => $record->lesson_noi,
            'voice_url' => $voiceUrl,
            'is_video' => $isVideo
        ]);

        return Inertia::render('Lesson/EditVoice', [
            'query' => $request->query(),
            'voice_show' => $voiceUrl,
            'voice' => $record->lesson_noi,
            'isVideo' => $isVideo
        ]);
    }

    public function new(Request $request): Response
    {
        //        todo get data for input select
        return Inertia::render('Lesson/NewVideo', [
            'query' => $request->query(),
            //            ...
        ]);
    }

    public function history(Request $request): Response
    {
        //        todo data for list video
        return Inertia::render('Lesson/HistoryVideo', [
            'query' => $request->query(),
            //            ...
        ]);
    }
}
