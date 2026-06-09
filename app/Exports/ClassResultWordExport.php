<?php

namespace App\Exports;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassResultWordExport
{
    public function __construct(
        private array $rows,
        private string $appName = '',
        private string $className = '',
        private int $tabId = 2
    ) {}

    public function download(string $fileName): StreamedResponse
    {
        $phpWord = new PhpWord();

        // Default font
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        // A4 portrait section with margins (twips: 1 inch = 1440 twips)
        $section = $phpWord->addSection([
            'paperSize'    => 'A4',
            'orientation'  => 'portrait',
            'marginTop'    => Converter::cmToTwip(2),
            'marginBottom' => Converter::cmToTwip(2),
            'marginLeft'   => Converter::cmToTwip(2),
            'marginRight'  => Converter::cmToTwip(2),
        ]);

        // Header: app name (right aligned, italic, small)
        if ($this->appName !== '') {
            $section->addText(
                htmlspecialchars($this->appName),
                ['italic' => true, 'size' => 11, 'color' => '555555'],
                ['alignment' => 'right', 'spaceAfter' => 0]
            );
        }

        // Main title
        $title = $this->tabId == 1
            ? 'BÁO CÁO KẾT QUẢ LUYỆN TẬP TỰ DO'
            : 'BÁO CÁO NHIỆM VỤ ĐƯỢC GIAO';
        $section->addText(
            $title,
            ['bold' => true, 'size' => 16, 'color' => '2C75E3'],
            ['alignment' => 'center', 'spaceAfter' => 120]
        );

        // Subtitle: class name
        if ($this->className !== '') {
            $section->addText(
                'Lớp: ' . $this->className,
                ['bold' => true, 'size' => 13],
                ['alignment' => 'center', 'spaceAfter' => 120]
            );
        }

        // Date
        $section->addText(
            'Ngày xuất báo cáo: ' . date('d/m/Y'),
            ['italic' => true, 'size' => 11, 'color' => '666666'],
            ['alignment' => 'right', 'spaceAfter' => 240]
        );

        // Table
        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => '999999',
            'cellMargin'  => 80,
            'alignment'   => 'center',
        ];
        $phpWord->addTableStyle('ResultTable', $tableStyle);
        $table = $section->addTable('ResultTable');

        $headerFont   = ['bold' => true, 'color' => 'FFFFFF', 'size' => 12];
        $headerCell   = ['bgColor' => '2C75E3', 'valign' => 'center'];
        $headerPara   = ['alignment' => 'center', 'spaceAfter' => 0];
        $bodyPara     = ['alignment' => 'left', 'spaceAfter' => 0];
        $bodyParaCtr  = ['alignment' => 'center', 'spaceAfter' => 0];
        $bodyCell     = ['valign' => 'center'];

        if ($this->tabId == 1) {
            $widths   = [800, 5000, 2000, 2200];
            $headers  = ['STT', 'Họ và tên', 'Thành tích (Sao)', 'Thời gian làm'];
        } else {
            $widths   = [800, 6500, 2700];
            $headers  = ['STT', 'Danh sách nhiệm vụ', 'Thời hạn'];
        }

        $table->addRow(500, ['tblHeader' => true]);
        foreach ($headers as $i => $h) {
            $table->addCell($widths[$i], $headerCell)->addText($h, $headerFont, $headerPara);
        }

        foreach ($this->rows as $index => $row) {
            $table->addRow();
            $table->addCell($widths[0], $bodyCell)->addText((string)($index + 1), [], $bodyParaCtr);
            if ($this->tabId == 1) {
                $table->addCell($widths[1], $bodyCell)->addText(htmlspecialchars($row['name'] ?? ''), [], $bodyPara);
                $table->addCell($widths[2], $bodyCell)->addText((string)($row['star_count'] ?? ''), [], $bodyParaCtr);
                $table->addCell($widths[3], $bodyCell)->addText(htmlspecialchars($row['time_text'] ?? ''), [], $bodyParaCtr);
            } else {
                $table->addCell($widths[1], $bodyCell)->addText(htmlspecialchars($row['name'] ?? ''), [], $bodyPara);
                $table->addCell($widths[2], $bodyCell)->addText(htmlspecialchars($row['duration'] ?? ''), [], $bodyParaCtr);
            }
        }

        // Footer / signature
        $section->addTextBreak(2);
        $section->addText(
            'Người lập báo cáo',
            ['italic' => true, 'size' => 12],
            ['alignment' => 'right']
        );
        $section->addTextBreak(3);
        $section->addText(
            '(Ký, ghi rõ họ tên)',
            ['italic' => true, 'size' => 11, 'color' => '666666'],
            ['alignment' => 'right']
        );

        return response()->streamDownload(function () use ($phpWord) {
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}
