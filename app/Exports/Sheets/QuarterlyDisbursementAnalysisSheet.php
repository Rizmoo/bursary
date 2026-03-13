<?php

namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuarterlyDisbursementAnalysisSheet implements FromView, WithEvents, WithStyles, WithTitle
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        protected array $data,
    ) {}

    public function view(): View
    {
        return view('exports.quarterly.disbursement-analysis', [
            'data' => $this->data,
        ]);
    }

    public function title(): string
    {
        return 'Disbursement Analysis';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true, 'size' => 11]],
            4 => ['font' => ['bold' => true, 'size' => 11]],
            6 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:N1');
                $sheet->mergeCells('A2:N2');
                $sheet->mergeCells('A3:N3');
                $sheet->mergeCells('A4:N4');

                $sheet->mergeCells('A6:A7');
                $sheet->mergeCells('B6:C6');
                $sheet->mergeCells('D6:E6');
                $sheet->mergeCells('F6:G6');
                $sheet->mergeCells('H6:I6');
                $sheet->mergeCells('J6:J7');
                $sheet->mergeCells('K6:K7');
                $sheet->mergeCells('L6:L7');
                $sheet->mergeCells('M6:M7');
                $sheet->mergeCells('N6:N7');

                foreach (range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getRowDimension(6)->setRowHeight(35);
                $sheet->getRowDimension(7)->setRowHeight(28);

                $sheet->getStyle('A1:N4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A6:N7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A6:N7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A6:N7')->getAlignment()->setWrapText(true);

                $sheet->getStyle('A6:N8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A6:N7')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFECEFF3');

                $sheet->getStyle('B8:B8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('D8:D8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('F8:F8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('H8:H8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

                $sheet->getStyle('C8:C8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('E8:E8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('G8:G8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('I8:N8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $sheet->getStyle('C8:N8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
