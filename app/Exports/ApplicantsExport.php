<?php

namespace App\Exports;

use App\Models\Applicant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ApplicantsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @param array<int> $applicantIds
     * @param array<string, mixed> $filters
     */
    public function __construct(
        protected int $wardId,
        protected array $applicantIds = [],
        protected array $filters = [],
    ) {}

    public function collection(): Collection
    {
        $query = Applicant::query()
            ->with(['institution', 'financialYear'])
            ->where('ward_id', $this->wardId)
            ->orderBy('institution_id')
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($this->applicantIds !== []) {
            $query->whereKey($this->applicantIds);
        }

        if (filled($this->filters['financial_year_id'] ?? null)) {
            $query->where('financial_year_id', (int) $this->filters['financial_year_id']);
        }

        if (filled($this->filters['institution_category_id'] ?? null)) {
            $query->whereHas('institution', fn ($q) => $q->where('category_id', (int) $this->filters['institution_category_id']));
        }

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

        if (($this->filters['has_disability_filter'] ?? 'any') === 'yes') {
            $query->where('has_disability', true);
        }
        if (($this->filters['has_disability_filter'] ?? 'any') === 'no') {
            $query->where('has_disability', false);
        }

        if (($this->filters['has_disabled_parent_filter'] ?? 'any') === 'yes') {
            $query->where('has_disabled_parent', true);
        }
        if (($this->filters['has_disabled_parent_filter'] ?? 'any') === 'no') {
            $query->where('has_disabled_parent', false);
        }

        if (! empty($this->filters['beneficiaries_only'])) {
            $query->where('amount_awarded', '>', 0);
        }

        return $query->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Application No.',
            'Admission No.',
            'Name',
            'Gender',
            'Institution Name',
            'Financial Year',
            'Amount Awarded',
        ];
    }

    /**
     * @param Applicant $row
     * @return array<int, string|float>
     */
    public function map($row): array
    {
        $name = trim(collect([
            $row->first_name,
            $row->other_name,
            $row->last_name,
        ])->filter()->implode(' '));

        return [
            (string) ($row->application_number ?? ''),
            (string) ($row->admission_number ?? ''),
            $name,
            (string) ($row->gender ?? ''),
            (string) ($row->institution?->name ?? ''),
            (string) ($row->financialYear?->name ?? ''),
            (float) $row->amount_awarded,
        ];
    }
}
