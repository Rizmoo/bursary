<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Models\Institution;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InstitutionChequeWritingTemplateExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        protected int $wardId,
        protected int $financialYearId,
        protected array $filters = [],
    ) {}

    public function collection(): Collection
    {
        $institutions = Institution::query()
            ->where('ward_id', $this->wardId)
            ->when(filled($this->filters['institution_category_id'] ?? null), fn ($query) => $query->where('category_id', (int) $this->filters['institution_category_id']))
            ->with(['applicants' => function ($query): void {
                $query->where('financial_year_id', $this->financialYearId)
                    ->where('amount_awarded', '>', 0)
                    ->whereNotNull('admission_number')
                    ->where('admission_number', '!=', '')
                    ->whereDoesntHave('institutionCheques');

                $needMin = $this->filters['need_min'] ?? null;
                $needMax = $this->filters['need_max'] ?? null;
                if (filled($needMin) && filled($needMax)) {
                    $query->whereBetween('need_assessment', [(float) $needMin, (float) $needMax]);
                } elseif (filled($needMin)) {
                    $query->where('need_assessment', '>=', (float) $needMin);
                } elseif (filled($needMax)) {
                    $query->where('need_assessment', '<=', (float) $needMax);
                }

                if (! empty($this->filters['orphans_only'])) {
                    $query->whereIn('orphan_status', [Applicant::ORPHAN_STATUS_PARTIAL, Applicant::ORPHAN_STATUS_TOTAL]);
                }
            }])
            ->orderBy('name')
            ->get()
            ->map(function (Institution $institution): Institution {
                $students = $institution->applicants->count();
                $amount = (float) $institution->applicants->sum('amount_awarded');

                $institution->setAttribute('students_for_cheque', $students);
                $institution->setAttribute('amount_for_cheque', $amount);

                return $institution;
            })
            ->filter(fn (Institution $institution): bool => (int) $institution->getAttribute('students_for_cheque') > 0)
            ->values();

        return $institutions;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Institution ID',
            'Institution Name',
            'Students',
            'Amount',
            'Cheque Number',
        ];
    }

    /**
     * @param Institution $row
     * @return array<int, int|string|float>
     */
    public function map($row): array
    {
        return [
            (int) $row->id,
            (string) $row->name,
            (int) $row->getAttribute('students_for_cheque'),
            (float) $row->getAttribute('amount_for_cheque'),
            '',
        ];
    }
}
