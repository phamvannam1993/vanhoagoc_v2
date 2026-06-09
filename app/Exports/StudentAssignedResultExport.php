<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StudentAssignedResultExport implements FromView
{
    public function __construct(
        private array $rows,
        private string $studentName = '',
        private string $className = ''
    ) {}

    public function view(): View
    {
        return view('exports.student_assigned_result', [
            'rows'        => $this->rows,
            'studentName' => $this->studentName,
            'className'   => $this->className,
        ]);
    }
}
