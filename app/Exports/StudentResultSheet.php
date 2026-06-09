<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class StudentResultSheet implements FromView, WithTitle
{
    public function __construct(
        private array $rows,
        private string $studentName = '',
        private string $className = ''
    ) {}

    public function view(): View
    {
        return view('exports.student_result', [
            'rows'        => $this->rows,
            'studentName' => $this->studentName,
            'className'   => $this->className,
        ]);
    }

    public function title(): string
    {
        return 'Chi tiết luyện tập';
    }
}
