<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationalContent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EducationalContentController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/EducationalContent/Index', [
            'query' => $request->query()
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/EducationalContent/Create');
    }

    public function edit(Request $request): Response
    {
        $item = EducationalContent::findOrFail($request->query('id'));
        return Inertia::render('Admin/EducationalContent/Edit', ['item' => $item]);
    }

    public function jsonList(Request $request)
    {
        $query = EducationalContent::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $data = $query->orderBy('id')->paginate($request->input('per_page', 20));

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        if ($request->filled('id')) {
            EducationalContent::findOrFail($request->id)->update($data);
        } else {
            EducationalContent::create($data);
        }

        return response()->json(['status' => true]);
    }

    public function delete($id)
    {
        EducationalContent::findOrFail($id)->delete();
        return response()->json(['status' => true]);
    }
}
