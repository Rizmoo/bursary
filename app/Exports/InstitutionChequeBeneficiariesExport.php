<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Models\InstitutionCheque;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InstitutionChequeBeneficiariesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected InstitutionCheque $cheque) {}

    public function collection(): Collection
    {
        return $this->cheque->applicants()
            ->with('institution')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Admission Number',
            'Name',
            'Institution Name',
            'Amount',
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
            (string) ($row->admission_number ?? ''),
            $name,
            (string) ($row->institution?->name ?? $this->cheque->institution?->name ?? ''),
            (float) $row->amount_awarded,
        ];
    }
}
