<?php

namespace App\Exports;

use App\Models\Applicant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class ApplicantsBeneficiariesExport implements FromView, WithEvents, WithColumnWidths, WithTitle
{
    /**
     * @param array<int> $applicantIds
     * @param array<string, mixed> $filters
     */
    public function __construct(
        protected int $wardId,
        protected array $applicantIds = [],
        protected string $wardName,
        protected array $filters = [],
    ) {}

    public function view(): View
    {
        $rows = Applicant::query()
            ->with(['institution', 'financialYear'])
            ->where('ward_id', $this->wardId)
            ->where('amount_awarded', '>', 0)
            ->orderBy('institution_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->when($this->applicantIds !== [], fn ($query) => $query->whereKey($this->applicantIds))
            ->when(filled($this->filters['financial_year_id'] ?? null), fn ($query) => $query->where('financial_year_id', (int) $this->filters['financial_year_id']))
            ->when(filled($this->filters['institution_category_id'] ?? null), fn ($query) => $query->whereHas('institution', fn ($q) => $q->where('category_id', (int) $this->filters['institution_category_id'])))
            ->when(filled($this->filters['need_min'] ?? null) && filled($this->filters['need_max'] ?? null), fn ($query) => $query->whereBetween('need_assessment', [(float) $this->filters['need_min'], (float) $this->filters['need_max']]))
            ->when(filled($this->filters['need_min'] ?? null) && blank($this->filters['need_max'] ?? null), fn ($query) => $query->where('need_assessment', '>=', (float) $this->filters['need_min']))
            ->when(filled($this->filters['need_max'] ?? null) && blank($this->filters['need_min'] ?? null), fn ($query) => $query->where('need_assessment', '<=', (float) $this->filters['need_max']))
            ->when(! empty($this->filters['orphans_only']), fn ($query) => $query->whereIn('orphan_status', [Applicant::ORPHAN_STATUS_PARTIAL, Applicant::ORPHAN_STATUS_TOTAL]))
            ->when(($this->filters['has_disability_filter'] ?? 'any') === 'yes', fn ($query) => $query->where('has_disability', true))
            ->when(($this->filters['has_disability_filter'] ?? 'any') === 'no', fn ($query) => $query->where('has_disability', false))
            ->when(($this->filters['has_disabled_parent_filter'] ?? 'any') === 'yes', fn ($query) => $query->where('has_disabled_parent', true))
            ->when(($this->filters['has_disabled_parent_filter'] ?? 'any') === 'no', fn ($query) => $query->where('has_disabled_parent', false))
            ->get();

        $financialYears = $rows->pluck('financialYear.name')->filter()->unique()->values();
        $financialYearLabel = $financialYears->count() === 1
            ? (string) $financialYears->first()
            : 'MULTIPLE FINANCIAL YEARS';

        return view('exports.applicants.beneficiaries', [
            'rows' => $rows,
            'wardName' => $this->wardName,
            'financialYearLabel' => $financialYearLabel,
        ]);
    }

    public function title(): string
    {
        return 'Beneficiaries';
    }

    /**
     * @return array<string, float>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 42,
            'C' => 12,
            'D' => 52,
            'E' => 14,
        ];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestRow = max(6, $sheet->getHighestRow());

                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                $sheet->mergeCells('A4:E4');

                $sheet->getStyle('A1:E4')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1:E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A5:E5')->getFont()->setBold(true);
                $sheet->getStyle('A5:E5')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE5E7EB');

                $sheet->getStyle("A5:E{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("A6:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("C6:C{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E6:E{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E6:E{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }
}
