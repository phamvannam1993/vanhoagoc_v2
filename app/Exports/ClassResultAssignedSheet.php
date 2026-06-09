<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClassResultAssignedSheet implements FromView, WithTitle
{
    public function __construct(
        private array $assignedTasks,
        private array $rankingByTask,
        private string $className = ''
    ) {}

    public function title(): string
    {
        return 'Nhiệm vụ được giao';
    }

    public function view(): View
    {
        return view('exports.class_result_assigned', [
            'assignedTasks' => $this->assignedTasks,
            'rankingByTask' => $this->rankingByTask,
            'className'     => $this->className,
        ]);
    }
}
