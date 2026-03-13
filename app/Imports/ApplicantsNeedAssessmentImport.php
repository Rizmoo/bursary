<?php

namespace App\Imports;

use App\Models\Applicant;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class ApplicantsNeedAssessmentImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
    public int $updatedCount = 0;
    public int $skippedCount = 0;
    public int $notFoundCount = 0;

    public function __construct(
        private readonly int $wardId,
        private readonly int $financialYearId,
        private readonly bool $overwriteExisting,
    ) {}

    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $admissionNumber = trim((string) (
            $data['admission_number']
            ?? $data['admission_no']
            ?? $data['adm_no']
            ?? $data['adm']
            ?? ''
        ));

        $scoreRaw = $data['need_assessment']
            ?? $data['need_score']
            ?? $data['score']
            ?? null;

        if ($admissionNumber === '' || $scoreRaw === null || $scoreRaw === '') {
            $this->skippedCount++;

            return;
        }

        if (! is_numeric($scoreRaw)) {
            $this->skippedCount++;

            return;
        }

        $score = max(0, min(100, (int) round((float) $scoreRaw)));

        $applicant = Applicant::query()
            ->where('ward_id', $this->wardId)
            ->where('financial_year_id', $this->financialYearId)
            ->where('admission_number', $admissionNumber)
            ->first();

        if (! $applicant) {
            $this->notFoundCount++;

            return;
        }

        if (! $this->overwriteExisting && (int) $applicant->need_assessment > 0) {
            $this->skippedCount++;

            return;
        }

        $applicant->update([
            'need_assessment' => $score,
        ]);

        $this->updatedCount++;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
