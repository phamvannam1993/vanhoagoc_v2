<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        // Anh truyền ra mảng có thuộc tính key và value
        return Inertia::render('Book/Index', [
            'listCourse' => [],
            'listBook' => []
        ]);
    }

    public function jsonList(Request $request, CourseService $courseService)
    {
        $params = $request->all();
        $list = $courseService->getList($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function create()
    {
        return Inertia::render('Book/Create', [
            'listApp' => [],
            'listBook' => [],
            'listClass' => [],
        ]);
    }

    public function edit($id, Request $request)
    {
        return Inertia::render('Book/Edit');
    }
}
