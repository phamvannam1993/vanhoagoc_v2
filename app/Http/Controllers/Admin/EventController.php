<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EventRanking;
use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Event;
use App\Models\Role;
use App\Repositories\EventPointRepository;
use App\Repositories\EventRepository;
use App\Services\AppService;
use App\Services\EventService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class EventController extends Controller
{
    public function __construct(
        private AppService $appService,
        private EventService $eventService,
        private EventRepository $eventRepository,
        private EventPointRepository $eventPointRepository
    )
    {
    }

    public function index(Request $request)
    {
        $entities = $this->eventService->getListPaginate($request->all());
        $entities->map(function($item){
            $item->start_datetime_label = $item->getStartDatetimeLabel();
            $item->end_datetime_label = $item->getEndDatetimeLabel();
            $item->status = $item->getEventStatus();
            $item->status_label = $item->getEventStatus(true);
            $item->can_delete = $item->canDelete();
            $item->class_name = $item->class?->name ?? '';
            return $item;
        });
        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Lấy dữ liệu thành công!',
                'entities' => $entities,
            ]);
        }

        return Inertia::render('Admin/Event/Index', [
            'query' => $request->query(),
            'entities' => $entities
        ]);
    }

    public function ranking($id, Request $request)
    {
        $event = $this->eventRepository->findOrFail($id);
        $request->merge(['event_id' => $id]);
        $entities = $this->eventPointRepository->getRanking($request->all(), true, ['user']);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Lấy dữ liệu thành công!',
                'entities' => $entities,
            ]);
        }

        return Inertia::render('Admin/Event/Ranking', [
            'event' => $event,
            'entities' => $entities
        ]);
    }

    public function exportRanking($id)
    {
        $event = $this->eventRepository->findOrFail($id);
        return Excel::download(new EventRanking($event->id), $event->name.'-'.$event->id. '.xlsx');
    }

    public function create(Request $request)
    {
        $apps = $this->appService->getListByRole();
        $apps = collect($apps)->map(function ($app) {
            return app(\App\Repositories\AppRepository::class)->find($app->id)
                ?? $app;
        });

        // Load books.weeks.practices for each allowed app
        $appIds = collect($apps)->pluck('id')->toArray();
        $appsWithData = \App\Models\App::with('books.weeks.practices')
            ->whereIn('id', $appIds)
            ->get();

        return Inertia::render('Admin/Event/Create', [
            'query' => $request->query(),
            'apps' => $appsWithData,
        ]);
    }

    public function edit($id, Request $request)
    {
        $appIds = collect($this->appService->getListByRole())->pluck('id')->toArray();
        $appsWithData = \App\Models\App::with('books.weeks.practices')
            ->whereIn('id', $appIds)
            ->get();

        $event = $this->eventRepository->findOrFail($id);
        $event->load('practices');
        $event->practice_ids = $event->practices->pluck('id');

        return Inertia::render('Admin/Event/Create', [
            'query' => $request->query(),
            'apps' => $appsWithData,
            'entity' => $event,
        ]);
    }

    public function getClassesByApp(Request $request)
    {
        $appId = $request->input('app_id');
        $user = auth()->user();

        if ($user->isTeacher()) {
            // Teacher: only classes they manage
            $classes = Classes::where('user_id', $user->id)
                ->where('app_id', $appId)
                ->get(['id', 'name']);
        } else {
            // Director / Admin: all classes in the app
            $classes = Classes::where('app_id', $appId)
                ->get(['id', 'name']);
        }

        return response()->json([
            'status' => true,
            'data' => $classes,
        ]);
    }

    public function store(Request $request)
    {
        $this->eventService->store($request->all());

        return redirect()->route('admins.event.index');
    }

    public function jsonUpdate(Request $request)
    {
        $this->eventService->jsonUpdate($request->all());

        return redirect()->route('admins.event.index');
    }

    public function activate($id)
    {
        $event = $this->eventRepository->findOrFail($id);

        if ($event->status !== \App\Enums\EventConstant::STATUS_DRAFT) {
            return response()->json([
                'status' => false,
                'message' => 'Chỉ có thể kích hoạt sự kiện đang ở trạng thái Nháp.',
            ], 422);
        }

        $event->update(['status' => \App\Enums\EventConstant::STATUS_ACTIVE]);

        return response()->json([
            'status' => true,
            'message' => 'Kích hoạt sự kiện thành công!',
        ]);
    }

    public function checkOverlap(Request $request)
    {
        $classId    = $request->input('class_id');
        $start      = $request->input('start_datetime');
        $end        = $request->input('end_datetime');
        $excludeId  = $request->input('exclude_id');

        if (!$classId || !$start || !$end) {
            return response()->json(['overlapping' => []]);
        }

        $query = \App\Models\Event::where('class_id', $classId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $overlapping = $query->get(['id', 'name', 'start_datetime', 'end_datetime'])
            ->map(fn($e) => [
                'id'   => $e->id,
                'name' => $e->name,
                'start' => \Carbon\Carbon::parse($e->start_datetime)->format('d/m/Y H:i'),
                'end'   => \Carbon\Carbon::parse($e->end_datetime)->format('d/m/Y H:i'),
            ]);

        return response()->json(['overlapping' => $overlapping]);
    }

    public function destroy($id, Request $request)
    {
        $event = $this->eventRepository->findOrFail($id);

        if (!$event->canDelete()) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể xóa sự kiện đang diễn ra.',
            ], 422);
        }

        $this->eventService->destroy($id);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Xóa thành công!',
            ]);
        }
    }
}
