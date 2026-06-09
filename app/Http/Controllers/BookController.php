<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\AppService;
use App\Services\BookService;
use App\Services\CopyDataService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookController extends Controller
{
    protected $appService;
    protected $bookService;
    protected $copyDataService;
    public function __construct(AppService $appService, BookService $bookService, CopyDataService $copyDataService)
    {
        $this->appService = $appService;
        $this->bookService = $bookService;
        $this->copyDataService = $copyDataService;
    }
    public function index(Request $request, AppService $appService)
    {
        $appId = $request->get('appId');
        $appList = $appService->getListFull();

        return Inertia::render('Book/Index', [
            'appList' => $appList,
            'appId' => $appId,
            'query' => $request->query()
        ]);
    }

    public function jsonList(Request $request, BookService $bookService)
    {
        $params = $request->all([
            'search',
            'app_id',
            'bo_sach'
        ]);

        $list = $bookService->getList($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function create(AppService $appService, Request $request)
    {
        $appList = $appService->getListFull();

        return Inertia::render('Book/Create', [
            'appList' => $appList,
            'query' => $request->query(),
        ]);
    }

    public function store(Request $request, BookService $bookService)
    {
        $params = $request->all([
            'title',
            'name',
            'app_id',
            'bo_sach',
            'lop',
            'img'
        ]);
        $params['sort_number'] = microtime(true) * 10000;
        $appId = $request->app_id;
        $result = $bookService->store($params);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('books.index', ['appId' => $appId])
                ]
            ]);
        }
    }

    public function edit($id, Request $request)
    {
        $record = $this->bookService->getDetail($id);
        $record->lop = filter_var($record->lop, FILTER_SANITIZE_NUMBER_INT);
        $record->img_show = $record->img ? Helper::getCloudFront($record->img) : null;
        $appList = $this->appService->getListFull();

        return Inertia::render('Book/Edit', [
            'appList' => $appList,
            'record' => $record,
            'query' => $request->query()
        ]);
    }
    public function detail($id, Request $request)
    {
        $record = $this->bookService->getDetail($id);
        $appList = $this->appService->getListFull();

        return Inertia::render('Book/Detail', [
            'appList' => $appList,
            'record' => $record,
            'query' => $request->query()
        ]);
    }

    public function update(Request $request, BookService $bookService)
    {
        $params = $request->all([
            'title',
            'name',
            'app_id',
            'bo_sach',
            'lop',
            'id',
            'delete_file',
        ]);

        $params['img'] = $request->img ?? null;

        $appId = $request->app_id;
        $result = $bookService->update($params);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('books.index', ['appId' => $appId])
                ]
            ]);
        }
    }

    public function delete($id, BookService $bookService)
    {
        $result = $bookService->delete($id);

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

    public function visible(Request $request, BookService $bookService)
    {
        $params = $request->all([
            'status',
            'id'
        ]);

        $bookService->updateVisible($params);

        return response()->json([
            'status' => true
        ]);
    }

    public function updatePosition(Request $request)
    {
        $fromId = $request->from_id;
        $toId = $request->to_id;
        $recordFrom = $this->bookService->getDetail($fromId);
        $recordTo = $this->bookService->getDetail($toId);
        $prevToId = $this->bookService->getPrevById($recordTo);
        $nextToId = $this->bookService->getNextById($recordTo);

        if ($recordFrom->sort_number < $recordTo->sort_number) {
            $this->bookService->updateNextPosition($recordFrom, $recordTo, $nextToId);
        } else {
            $this->bookService->updatePrevPosition($recordFrom, $recordTo, $prevToId);
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function copyData(Request $request) {
        $this->copyDataService->handleCopyBook($request->book_ids, $request->to_app_id);
        return response()->json([
            'status' => true
        ]);
    }
}
