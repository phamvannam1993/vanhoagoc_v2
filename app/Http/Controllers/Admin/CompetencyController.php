<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competency;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetencyController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Competency/Index', [
            'query' => $request->query()
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Competency/Create');
    }

    public function edit(Request $request): Response
    {
        $item = Competency::findOrFail($request->query('id'));
        return Inertia::render('Admin/Competency/Edit', ['item' => $item]);
    }

    public function jsonList(Request $request)
    {
        $query = Competency::query();

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
            Competency::findOrFail($request->id)->update($data);
        } else {
            Competency::create($data);
        }

        return response()->json(['status' => true]);
    }

    public function delete($id)
    {
        Competency::findOrFail($id)->delete();
        return response()->json(['status' => true]);
    }
}
