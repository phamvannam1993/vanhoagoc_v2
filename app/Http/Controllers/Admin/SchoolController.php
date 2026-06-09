<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SchoolController extends Controller
{
    protected $appService;

    public function __construct(AppService $appService)
    {
        $this->appService = $appService;
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/School/Index', [
            'query' => $request->query()
        ]);
    }

    public function jsonList(Request $request)
    {
        $params = $request->all();
        $list = $this->appService->getList($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }
}
