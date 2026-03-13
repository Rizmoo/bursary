<?php

namespace App\Imports;

use App\Models\Applicant;
use App\Models\Institution;
use App\Models\Ward;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ApplicantsImport implements ToCollection
{
    public int $importedCount = 0;
    public int $skippedCount  = 0;

    public function __construct(
        private readonly int $wardId,
        private readonly int $financialYearId,
        private readonly int $categoryId,
    ) {}

    /**
     * The spreadsheet has a multi-row title block before the real data.
     * We detect the actual header row by looking for "ADM" in the second column,
     * then process all rows that follow.
     */
    public function collection(Collection $rows): void
    {
        $dataStarted = false;

        foreach ($rows as $row) {
            $values = array_values($row->toArray());

            // Find the header row (contains "ADM" or "ADM. NO." in column B)
            if (! $dataStarted) {
                $colB = Str::upper(trim((string) ($values[1] ?? '')));
                if (Str::contains($colB, 'ADM')) {
                    $dataStarted = true;
                }
                continue;
            }

            // Skip empty rows
            $admNo   = trim((string) ($values[1] ?? ''));
            $rawName = trim((string) ($values[2] ?? ''));
            $gender  = Str::upper(trim((string) ($values[3] ?? '')));
            $instName = trim((string) ($values[4] ?? ''));

            if ($rawName === '' && $instName === '') {
                continue;
            }

            // Parse name: first word → first_name, last word → last_name, rest → other_name
            $nameParts = preg_split('/\s+/', $rawName, -1, PREG_SPLIT_NO_EMPTY);
            $firstName  = Str::title($nameParts[0] ?? '');
            $lastName   = count($nameParts) > 1 ? Str::title(end($nameParts)) : '';
            $otherName  = count($nameParts) > 2
                ? Str::title(implode(' ', array_slice($nameParts, 1, -1)))
                : null;

            $genderNorm = match ($gender) {
                'M'     => 'male',
                'F'     => 'female',
                default => null,
            };

            // Find or create institution scoped to this ward and category
            $institution = Institution::firstOrCreate(
                [
                    'ward_id' => $this->wardId,
                    'name'    => $instName,
                ],
                [
                    'code'        => $this->generateCode($instName),
                    'category_id' => $this->categoryId,
                ]
            );

            // Skip duplicate applicants (same admission number in same financial year)
            if ($admNo !== '' && Applicant::where('admission_number', $admNo)
                ->where('financial_year_id', $this->financialYearId)
                ->exists()) {
                $this->skippedCount++;
                continue;
            }

            Applicant::create([
                'ward_id'          => $this->wardId,
                'financial_year_id'=> $this->financialYearId,
                'institution_id'   => $institution->id,
                'application_number' => 'IMP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                'admission_number' => $admNo ?: null,
                'first_name'       => $firstName,
                'last_name'        => $lastName,
                'other_name'       => $otherName,
                'name'             => $rawName,
                'gender'           => $genderNorm,
                'orphan_status'    => Applicant::ORPHAN_STATUS_NONE,
            ]);

            $this->importedCount++;
        }
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
}
