<?php

namespace App\Http\Controllers;

use App\Models\Practice;
use App\Repositories\BookRepository;
use App\Repositories\WeekRepository;
use App\Services\CopyDataService;
use App\Services\PracticeService;
use App\Services\WeekService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends BaseModuleController
{
    protected $practiceService;
    protected $weekService;

    protected $weekRepository;
    protected $copyDataService;

    public function __construct(
        PracticeService $practiceService,
        WeekService $weekService,
        WeekRepository $weekRepository,
        CopyDataService $copyDataService)
    {
        $this->practiceService = $practiceService;
        $this->weekService = $weekService;
        $this->weekRepository = $weekRepository;
        $this->copyDataService = $copyDataService;
    }
    public function index(Request $request): Response
    {
        $bookId = $request->book_id;
        $weekId = $request->get('week_id');
        $listWeek = $this->weekService->getListByBook($bookId);
        $week = $this->weekRepository->findOrFail($weekId);
        $week->load('book.app');

        return Inertia::render('Lesson/Index', [
            'query' => $request->query(),
            'listWeek' => $listWeek->toArray(),
            'week' => $week
        ]);
    }

    public function jsonList(Request $request)
    {
        $params = $request->all();

        $list = $this->practiceService->getList($params);
        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function create(Request $request)
    {
        $params = $request->all([
            'week_id',
            'app_id',
            'book_id'
        ]);

        $numberPractice = $this->practiceService->getNumberPracticeForCreate($params);

        return Inertia::render('Lesson/Create', [
            'query' => $params,
            'numberPractice' => $numberPractice
        ]);
    }

    /**
     * Màn "Tạo bài học bằng AI" (design Tạo bài giảng V3).
     * Render trang sinh nội dung (Bài đọc / Sách nói / Video / Bài tập) bằng AI cho 1 bài học.
     */
    public function aiCreate(Request $request): Response
    {
        $appId = $request->query('app_id');
        $bookId = $request->query('book_id');
        $weekId = $request->query('week_id');
        $practiceId = $request->query('practice_id');

        $week = $this->weekRepository->findOrFail($weekId);
        $week->load('book.app');

        $practice = $practiceId ? Practice::find($practiceId) : null;

        return Inertia::render('Lesson/AiCreate', [
            'app_id' => $appId,
            'book_id' => $bookId,
            'week_id' => $weekId,
            'practice_id' => $practiceId,
            'week' => $week,
            'practice' => $practice,
        ]);
    }

    public function store(Request $request)
    {
        $params = $request->all([
            'name',
            'numberPractice',
            'taptrung',
            'app_id',
            'book_id',
            'week_id',
            'avatar',
            'background',
            'color_not_practice',
            'color_done_practice'
        ]);
        $lastSort = Practice::orderByDesc('sort_number')->value('sort_number');
        $params['sort_number'] = $lastSort ? $lastSort + 1000 : 1000;
        $result = $this->practiceService->store($params);

        if (empty($result)) {
            return response()->json([
                'status' => false,
                'messages' => [
                    'error_field' => 'Thiếu thông tin cần thiết!'
                ]
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route(
                        'lessons.index',
                        [
                            'book_id' => $params['book_id'],
                            'week_id' => $params['week_id'],
                            'app_id' => $params['app_id']
                        ]
                    )
                ]
            ]);
        }
    }

    public function edit(Request $request)
    {
        $practice_id = $request->practice_id;
        $lesson = $this->practiceService->getDetail($practice_id);
        $params = $request->all([
            'week_id',
            'app_id',
            'book_id'
        ]);

        $listNumberPractice = $this->practiceService->getNumberPracticeEdit($params);

        return Inertia::render('Lesson/Edit', [
            'lesson' => $lesson,
            'query' => $request->query(),
            'listNumberPractice' => $listNumberPractice
        ]);
    }

    public function detail($id, Request $request)
    {
        $lesson = $this->practiceService->getDetail($id);
        $params = $request->all([
            'week_id',
            'app_id',
            'book_id'
        ]);

        $listNumberPractice = $this->practiceService->getNumberPracticeEdit($params);
        return Inertia::render('Lesson/Detail', [
            'lesson' => $lesson,
            'query' => $request->query(),
            'listNumberPractice' => $listNumberPractice
        ]);
    }

    public function update(Request $request)
    {
        $params = $request->all([
            'name',
            'numberPractice',
            'taptrung',
            'book_id',
            'week_id',
            'app_id',
            'id',
            'avatar',
            'background',
            'color_not_practice',
            'color_done_practice'
        ]);

        $result = $this->practiceService->update($params);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route(
                        'lessons.index',
                        [
                            'book_id' => $params['book_id'],
                            'week_id' => $params['week_id'],
                            'app_id' => $params['app_id']
                        ]
                    )
                ]
            ]);
        }
    }

    public function delete($id, PracticeService $practiceService)
    {
        $result = $practiceService->delete($id);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
            ]);
        }
    }

    public function deleteMultiple(Request $request, PracticeService $practiceService)
    {
        $ids = $request->input('ids');
        foreach($ids as $id) {
            $practiceService->delete($id);
        }
        return response()->json([
            'status' => true,
        ]);
    }

    public function visible(Request $request, PracticeService $practiceService)
    {
        $params = $request->all([
            'status',
            'id'
        ]);

        $practiceService->updateVisible($params);

        return response()->json([
            'status' => true
        ]);
    }

    public function updatePosition(Request $request)
    {
        $fromId = $request->from_id;
        $toId = $request->to_id;

        $this->practiceService->dragPosition($fromId, $toId);

        return response()->json([
            'status' => true
        ]);
    }

    public function resetPosition(Request $request)
    {
        $this->practiceService->resetPosition($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function copyData(Request $request) {

        $this->copyDataService->handleCopyWeek($request->practice_ids, $request->to_week_id);
        return response()->json([
            'status' => true
        ]);
    }
}
