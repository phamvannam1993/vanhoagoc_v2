<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\PracticeService;
use App\Services\ReadingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReadingController extends Controller
{
    public function __construct(
        private ReadingService $readingService,
        private PracticeService $practiceService
    )
    {}
    public function create(Request $request)
    {
        $id = $request->get('practice_id');

        $record = $this->practiceService->getDetail($id, ['practiceImages']);

        return Inertia::render('Lesson/CreateReading', [
            'query' => $request->query(),
            'practice_images' => $record->practiceImages
        ]);
    }

    public function store(Request $request, PracticeService $practiceService)
    {
        $params = $request->all([
            'practice_id',
            'content',
            'image_db',
            'pdf_db'
        ]);

        $filter = [
            'id' => $params['practice_id']
        ];
        $update = [
            'lesson_doc' => $params['content'],
            'img' => $params['image_db'] ?? null,
            'pdf' => $params['pdf_db'] ?? null,
        ];

        $practiceService->storeByConditions($filter, $update);

        return response()->json([
            'status' => true,
        ]);
    }

    public function edit(Request $request, PracticeService $practiceService)
    {
        $id = $request->get('practice_id');

        $record = $practiceService->getDetail($id, ['practiceImages']);

        return Inertia::render('Lesson/EditReading', [
            'query' => $request->query(),
            'reading' => empty($record->lesson_doc) ? '' : $record->lesson_doc,
            'image_show' => $record->img ? Helper::getCloudFront($record->img) : '',
            'pdf_show' => $record->pdf ? Helper::getCloudFront($record->pdf) : '',
            'image_db' => $record->img ?? '',
            'pdf_db' => $record->pdf ?? '',
            'practice_images' => $record->practiceImages
        ]);
    }

    public function update() {}

    public function updateImage(Request $request)
    {
        try {
            $this->readingService->uploadImage(
                $request->practice_id,
                $request->file('files'),
                json_decode($request->get('remove_entities'), true)
            );
            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thành công',
            ]);
        } catch (\Exception $e) {
            Helper::logException($e);
            return response()->json([
                'status' => false,
                'message' => 'Cập nhật thành công',
            ], 500);
        }
    }
}
