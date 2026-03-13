<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Institution;
use App\Models\InstitutionCheque;
use App\Models\Ward;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstitutionMergeService
{
    /**
     * @return array{applicants_updated:int, cheques_updated:int}
     */
    public function merge(Ward $ward, int $sourceInstitutionId, int $targetInstitutionId): array
    {
        if ($sourceInstitutionId === $targetInstitutionId) {
            throw ValidationException::withMessages([
                'institution' => 'Source and target institutions must be different.',
            ]);
        }

        $source = Institution::query()
            ->where('ward_id', $ward->getKey())
            ->findOrFail($sourceInstitutionId);

        $target = Institution::query()
            ->where('ward_id', $ward->getKey())
            ->findOrFail($targetInstitutionId);

        return DB::transaction(function () use ($source, $target): array {
            $applicantsUpdated = Applicant::query()
                ->where('institution_id', $source->getKey())
                ->update(['institution_id' => $target->getKey()]);

            $chequesUpdated = InstitutionCheque::query()
                ->where('institution_id', $source->getKey())
                ->update(['institution_id' => $target->getKey()]);

            $source->delete();

            return [
                'applicants_updated' => $applicantsUpdated,
                'cheques_updated' => $chequesUpdated,
            ];
        });
    }
}
