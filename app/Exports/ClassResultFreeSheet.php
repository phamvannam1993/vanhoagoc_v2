<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ClassResultFreeSheet implements FromView, WithTitle
{
    public function __construct(
        private array $freeStudents,
        private string $className = ''
    ) {}

    public function title(): string
    {
        return 'Luyện tập tự do';
    }

    public function view(): View
    {
        return view('exports.class_result_free', [
            'students'  => $this->freeStudents,
            'className' => $this->className,
        ]);
    }
}
