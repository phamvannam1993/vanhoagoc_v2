<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PointDetailExport implements FromView
{
    public function __construct(
        private array $rows,
        private string $title,
        private string $studentName,
        private string $nameApp
    ) {}

    public function view(): View
    {
        return view('exports.point_detail', [
            'rows'        => $this->rows,
            'title'       => $this->title,
            'studentName' => $this->studentName,
            'nameApp'     => $this->nameApp,
        ]);
    }
}
