<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClassResultExport implements FromView, WithEvents
{
    public function __construct(
        private array $rows,
        private string $className = '',
        private int $tabId = 2
    ) {}

    public function view(): View
    {
        if ($this->tabId == 1) {
            return view('exports.class_result_free_simple', [
                'rows'      => $this->rows,
                'className' => $this->className,
            ]);
        }
        return view('exports.class_result_assigned_simple', [
            'rows'      => $this->rows,
            'className' => $this->className,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // A4 portrait, fit to width
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // Margins
                $margins = $sheet->getPageMargins();
                $margins->setTop(0.5);
                $margins->setBottom(0.5);
                $margins->setLeft(0.5);
                $margins->setRight(0.5);

                // Center horizontally on printed page
                $sheet->getPageSetup()->setHorizontalCentered(true);

                $colCount = $this->tabId == 1 ? 4 : 3;
                $lastCol  = $this->tabId == 1 ? 'D' : 'C';
                $highestRow = $sheet->getHighestRow();

                // Title row (row 1) — merged across columns
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2C75E3'],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Header row (row 2)
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '5FB2FF'],
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // Body borders + alignment
                if ($highestRow >= 3) {
                    $sheet->getStyle("A3:{$lastCol}{$highestRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                    // Center STT column
                    $sheet->getStyle("A3:A{$highestRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Apply borders to header too
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                    ],
                ]);

                // Column widths tuned for A4 portrait
                if ($this->tabId == 1) {
                    $sheet->getColumnDimension('A')->setWidth(8);
                    $sheet->getColumnDimension('B')->setWidth(35);
                    $sheet->getColumnDimension('C')->setWidth(18);
                    $sheet->getColumnDimension('D')->setWidth(20);
                } else {
                    $sheet->getColumnDimension('A')->setWidth(8);
                    $sheet->getColumnDimension('B')->setWidth(55);
                    $sheet->getColumnDimension('C')->setWidth(30);
                }

                // Repeat header rows on every printed page
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 2);
            },
        ];
    }
}
