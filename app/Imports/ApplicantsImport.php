<?php

namespace App\Imports;

use App\Models\Applicant;
use App\Models\Institution;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class ApplicantsImport implements OnEachRow, WithBatchInserts, WithChunkReading
{
    public int $importedCount = 0;
    public int $skippedCount  = 0;

    protected bool $dataStarted = false;

    public function __construct(
        private readonly int $wardId,
        private readonly int $financialYearId,
        private readonly int $categoryId,
    ) {}

    public function onRow(Row $row): void
    {
        $values = array_values($row->toArray());

        // Find the header row (contains "ADM" or "ADM. NO." in column B)
        if (! $this->dataStarted) {
            $colB = Str::upper(trim((string) ($values[1] ?? '')));
            if (Str::contains($colB, 'ADM')) {
                $this->dataStarted = true;
            }

            return;
        }

        // Skip empty rows
        $admNo    = trim((string) ($values[1] ?? ''));
        $rawName  = trim((string) ($values[2] ?? ''));
        $gender   = Str::upper(trim((string) ($values[3] ?? '')));
        $instName = trim((string) ($values[4] ?? ''));

        if ($rawName === '' && $instName === '') {
            return;
        }

        // Parse name: first word → first_name, last word → last_name, rest → other_name
        $nameParts = preg_split('/\s+/', $rawName, -1, PREG_SPLIT_NO_EMPTY);
        $firstName = Str::title($nameParts[0] ?? '');
        $lastName = count($nameParts) > 1 ? Str::title(end($nameParts)) : '';
        $otherName = count($nameParts) > 2
            ? Str::title(implode(' ', array_slice($nameParts, 1, -1)))
            : null;

        $genderNorm = match ($gender) {
            'M' => 'male',
            'F' => 'female',
            default => null,
        };

        if ($instName === '') {
            $this->skippedCount++;

            return;
        }

        // Find or create institution scoped to this ward and category
        $institution = $this->resolveInstitution($instName);

        // Skip duplicate applicants (same admission number in same financial year)
        if ($admNo !== '' && Applicant::where('admission_number', $admNo)
            ->where('financial_year_id', $this->financialYearId)
            ->exists()) {
            $this->skippedCount++;

            return;
        }

        Applicant::create([
            'ward_id' => $this->wardId,
            'financial_year_id' => $this->financialYearId,
            'institution_id' => $institution->id,
            'application_number' => 'IMP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
            'admission_number' => $admNo ?: null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'other_name' => $otherName,
            'name' => $rawName,
            'gender' => $genderNorm,
            'orphan_status' => Applicant::ORPHAN_STATUS_NONE,
        ]);

        $this->importedCount++;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }

    private function generateCode(string $name): string
    {
        $base = Str::upper(Str::slug($name, ''));
        $code = Str::limit($base, 10, '');

        // Ensure uniqueness
        $original = $code;
        $counter  = 1;
        while (Institution::where('code', $code)->exists()) {
            $code = $original . $counter++;
        }

        return $code;
    }

    private function resolveInstitution(string $institutionName): Institution
    {
        $normalizedName = preg_replace('/\s+/', ' ', trim($institutionName)) ?: trim($institutionName);

        $existing = Institution::where('ward_id', $this->wardId)
            ->where('name', $normalizedName)
            ->first();

        if ($existing) {
            return $existing;
        }

        for ($attempt = 0; $attempt < 10; $attempt++) {
            try {
                return Institution::create([
                    'ward_id' => $this->wardId,
                    'name' => $normalizedName,
                    'code' => $this->generateCode($normalizedName),
                    'category_id' => $this->categoryId,
                ]);
            } catch (QueryException $exception) {
                if ($exception->getCode() !== '23000') {
                    throw $exception;
                }

                $existingAfterConflict = Institution::where('ward_id', $this->wardId)
                    ->where('name', $normalizedName)
                    ->first();

                if ($existingAfterConflict) {
                    return $existingAfterConflict;
                }
            }
        }

        return Institution::create([
            'ward_id' => $this->wardId,
            'name' => $normalizedName,
            'code' => Str::upper(Str::random(12)),
            'category_id' => $this->categoryId,
        ]);
    }
}
