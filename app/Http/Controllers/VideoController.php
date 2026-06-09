<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\MediaHelper;
use App\Services\PracticeService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Lesson/CreateVideo', [
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
            'lesson_video' => $params['content'] ?? null
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
        $isYouTubeVideo = MediaHelper::isYouTubeUrl($record->lesson_video);

        return Inertia::render('Lesson/EditVideo', [
            'query' => $request->query(),
            'video' => $isYouTubeVideo === 0 ?  $record->lesson_video : Helper::getCloudFront($record->lesson_video),
            'isYouTubeVideo' => $isYouTubeVideo === 0
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
