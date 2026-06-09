<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentResultExport implements WithMultipleSheets
{
    public function __construct(
        private array $rows,
        private string $studentName = '',
        private string $className = ''
    ) {}

    public function sheets(): array
    {
        return [
            new StudentResultSheet($this->rows, $this->studentName, $this->className),
        ];
    }
}
