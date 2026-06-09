<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competency;
use App\Models\CompetencyComponent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetencyComponentController extends Controller
{
    public function index(Request $request): Response
    {
        $competency = Competency::findOrFail($request->query('competency_id'));
        return Inertia::render('Admin/CompetencyComponent/Index', [
            'query' => $request->query(),
            'competency' => $competency,
        ]);
    }

    public function create(Request $request): Response
    {
        $competency = Competency::findOrFail($request->query('competency_id'));
        return Inertia::render('Admin/CompetencyComponent/Create', [
            'competency' => $competency,
        ]);
    }

    public function edit(Request $request): Response
    {
        $item = CompetencyComponent::findOrFail($request->query('id'));
        $competency = Competency::findOrFail($item->competency_id);
        return Inertia::render('Admin/CompetencyComponent/Edit', [
            'item' => $item,
            'competency' => $competency,
        ]);
    }

    public function jsonList(Request $request)
    {
        $query = CompetencyComponent::where('competency_id', $request->competency_id);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $data = $query->orderBy('id')->paginate($request->input('per_page', 20));

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'competency_id' => 'required|exists:competencies,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        if ($request->filled('id')) {
            CompetencyComponent::findOrFail($request->id)->update($data);
        } else {
            CompetencyComponent::create($data);
        }

        return response()->json(['status' => true]);
    }

    public function delete($id)
    {
        CompetencyComponent::findOrFail($id)->delete();
        return response()->json(['status' => true]);
    }
}
