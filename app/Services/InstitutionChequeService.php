<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Institution;
use App\Models\InstitutionCheque;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstitutionChequeService
{
    public function createForInstitution(Institution $institution, int $financialYearId, array $applicantIds, array $attributes): InstitutionCheque
    {
        $applicantIds = array_values(array_unique(array_map('intval', $applicantIds)));

        if ($applicantIds === []) {
            throw ValidationException::withMessages([
                'applicant_ids' => 'Select at least one beneficiary for this cheque.',
            ]);
        }

        $applicants = Applicant::query()
            ->whereBelongsTo($institution)
            ->where('financial_year_id', $financialYearId)
            ->whereKey($applicantIds)
            ->get();

        if ($applicants->count() !== count($applicantIds)) {
            throw ValidationException::withMessages([
                'applicant_ids' => 'Some selected applicants do not belong to this institution and financial year.',
            ]);
        }

        return $this->createForApplicants($applicants, [
            ...$attributes,
            'financial_year_id' => $financialYearId,
        ]);
    }

    public function createForApplicants(EloquentCollection $applicants, array $attributes): InstitutionCheque
    {
        $applicants = $applicants->values();

        $this->validateApplicants($applicants);
        $this->validateChequeNumber($attributes['cheque_number']);

        /** @var Applicant $firstApplicant */
        $firstApplicant = $applicants->first();

        return DB::transaction(function () use ($applicants, $attributes, $firstApplicant): InstitutionCheque {
            $cheque = InstitutionCheque::create([
                'ward_id' => $firstApplicant->ward_id,
                'institution_id' => $firstApplicant->institution_id,
                'financial_year_id' => $firstApplicant->financial_year_id,
                'cheque_number' => $attributes['cheque_number'],
                'cheque_date' => $attributes['cheque_date'],
                'status' => InstitutionCheque::STATUS_PENDING,
                'total_amount' => $applicants->sum(fn (Applicant $applicant): float => (float) $applicant->amount_awarded),
                'returned_amount' => 0,
                'remarks' => $attributes['remarks'] ?? null,
            ]);

            $cheque->applicants()->attach($applicants->modelKeys());

            return $cheque->load(['applicants', 'institution', 'financialYear']);
        });
    }

    protected function validateApplicants(EloquentCollection $applicants): void
    {
        if ($applicants->isEmpty()) {
            throw ValidationException::withMessages([
                'records' => 'Select at least one applicant to assign a cheque.',
            ]);
        }

        if ($applicants->pluck('institution_id')->unique()->count() > 1) {
            throw ValidationException::withMessages([
                'records' => 'Selected applicants must belong to the same institution.',
            ]);
        }

        if ($applicants->pluck('financial_year_id')->unique()->count() > 1) {
            throw ValidationException::withMessages([
                'records' => 'Selected applicants must belong to the same financial year.',
            ]);
        }

        if ($applicants->pluck('ward_id')->unique()->count() > 1) {
            throw ValidationException::withMessages([
                'records' => 'Selected applicants must belong to the same ward.',
            ]);
        }

        if ($applicants->contains(fn (Applicant $applicant): bool => ! $applicant->isBeneficiary())) {
            throw ValidationException::withMessages([
                'records' => 'Only applicants with an awarded amount above KES 0 can be assigned to a cheque.',
            ]);
        }

        if ($applicants->contains(fn (Applicant $applicant): bool => ! $applicant->hasAdmissionNumber())) {
            throw ValidationException::withMessages([
                'records' => 'Applicants without admission numbers cannot be included in cheque assignments.',
            ]);
        }

        if (Applicant::query()->whereKey($applicants->modelKeys())->whereHas('institutionCheques')->exists()) {
            throw ValidationException::withMessages([
                'records' => 'One or more selected applicants already have a cheque assigned.',
            ]);
        }
    }

    protected function validateChequeNumber(string $chequeNumber): void
    {
        if (InstitutionCheque::query()->where('cheque_number', $chequeNumber)->exists()) {
            throw ValidationException::withMessages([
                'cheque_number' => 'That cheque number has already been used.',
            ]);
        }
    }
}
