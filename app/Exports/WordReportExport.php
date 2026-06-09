<?php

namespace App\Exports;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Generic Word (.docx) report.
 * - title: large blue centered heading
 * - subtitle / meta lines: appear under the title
 * - columns: ['STT', 'Họ và tên', ...]
 * - widths: cell widths in twips, same length as columns (defaults to even split)
 * - rows: list of arrays, each row aligned to columns positionally
 *   (use null/'' for empty cells)
 */
class WordReportExport
{
    public function __construct(
        private string $title,
        private array  $columns,
        private array  $rows,
        private array  $meta = [],       // extra lines under title, e.g. ['Lớp: 1A', 'Học sinh: Nguyễn A']
        private string $appName = '',    // small italic line at top right
        private array  $widths = [],     // optional column widths in twips
        private array  $centerCols = [0],// 0-based indexes of columns that should center-align
        private array  $mergeCols = []   // 0-based indexes of columns to vertically merge across all rows (value from row 0)
    ) {}

    public function download(string $fileName): StreamedResponse
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'paperSize'    => 'A4',
            'orientation'  => 'portrait',
            'marginTop'    => Converter::cmToTwip(2),
            'marginBottom' => Converter::cmToTwip(2),
            'marginLeft'   => Converter::cmToTwip(2),
            'marginRight'  => Converter::cmToTwip(2),
        ]);

        if ($this->appName !== '') {
            $section->addText(
                htmlspecialchars($this->appName),
                ['italic' => true, 'size' => 11, 'color' => '555555'],
                ['alignment' => 'right', 'spaceAfter' => 0]
            );
        }

        $section->addText(
            mb_strtoupper($this->title, 'UTF-8'),
            ['bold' => true, 'size' => 16, 'color' => '2C75E3'],
            ['alignment' => 'center', 'spaceAfter' => 160]
        );

        foreach ($this->meta as $line) {
            if ($line === null || $line === '') continue;
            $section->addText(
                htmlspecialchars((string)$line),
                ['bold' => true, 'size' => 12],
                ['alignment' => 'center', 'spaceAfter' => 80]
            );
        }

        $section->addText(
            'Ngày xuất báo cáo: ' . date('d/m/Y'),
            ['italic' => true, 'size' => 11, 'color' => '666666'],
            ['alignment' => 'right', 'spaceAfter' => 240]
        );

        $phpWord->addTableStyle('ReportTable', [
            'borderSize'  => 6,
            'borderColor' => '999999',
            'cellMargin'  => 80,
            'alignment'   => 'center',
        ]);
        $table = $section->addTable('ReportTable');

        $colCount = count($this->columns);
        $widths = $this->widths;
        if (count($widths) !== $colCount) {
            // A4 portrait usable width ~ 9000 twips
            $w = (int) floor(9000 / max(1, $colCount));
            $widths = array_fill(0, $colCount, $w);
        }

        $headerFont = ['bold' => true, 'color' => 'FFFFFF', 'size' => 12];
        $headerCell = ['bgColor' => '2C75E3', 'valign' => 'center'];
        $centerPara = ['alignment' => 'center', 'spaceAfter' => 0];
        $leftPara   = ['alignment' => 'left', 'spaceAfter' => 0];
        $bodyCell   = ['valign' => 'center'];

        $table->addRow(500, ['tblHeader' => true]);
        foreach ($this->columns as $i => $col) {
            $table->addCell($widths[$i], $headerCell)->addText(htmlspecialchars($col), $headerFont, $centerPara);
        }

        $totalRows = count($this->rows);
        foreach ($this->rows as $rowIdx => $row) {
            $table->addRow();
            for ($i = 0; $i < $colCount; $i++) {
                $isMerge = in_array($i, $this->mergeCols, true);
                $cellStyle = $bodyCell;

                if ($isMerge && $totalRows > 1) {
                    $cellStyle = array_merge($bodyCell, [
                        'vMerge' => $rowIdx === 0 ? 'restart' : 'continue',
                    ]);
                }

                $para = in_array($i, $this->centerCols, true) ? $centerPara : $leftPara;
                $cell = $table->addCell($widths[$i], $cellStyle);

                // For merged columns: only first row writes the value (from rows[0]); continuation rows leave cell empty
                if ($isMerge && $totalRows > 1 && $rowIdx !== 0) {
                    // continuation cell — no text
                } else {
                    $val = $row[$i] ?? '';
                    $cell->addText(htmlspecialchars((string)$val), [], $para);
                }
            }
        }

        return response()->streamDownload(function () use ($phpWord) {
            IOFactory::createWriter($phpWord, 'Word2007')->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}
