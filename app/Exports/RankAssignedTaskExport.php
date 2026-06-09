<?php

namespace App\Exports;

use App\Services\PointService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RankAssignedTaskExport implements FromView
{
    public function __construct(
        private int $classId,
        private int $practiceId,
        private string $title
    ) {}

    public function view(): View
    {
        $data = app(PointService::class)->rankAssignedTask($this->classId, $this->practiceId);

        return view('exports.rank_assigned_task', [
            'data' => $data,
            'title' => $this->title,
        ]);
    }
}
