<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Models\Institution;
use App\Models\InstitutionCategory;
use App\Models\Ward;
use App\Services\InstitutionChequeService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InstitutionChequeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_cheque_for_awarded_applicants_in_the_same_institution_and_year(): void
    {
        $service = app(InstitutionChequeService::class);
        [$institution, $financialYear] = $this->makeInstitutionContext();

        $firstApplicant = $this->makeApplicant($institution, $financialYear, [
            'application_number' => 'APP-001',
            'amount_awarded' => 1500,
        ]);

        $secondApplicant = $this->makeApplicant($institution, $financialYear, [
            'application_number' => 'APP-002',
            'amount_awarded' => 2500,
        ]);

        $cheque = $service->createForApplicants(new EloquentCollection([$firstApplicant, $secondApplicant]), [
            'cheque_number' => 'CHQ-001',
            'cheque_date' => now()->toDateString(),
            'remarks' => 'First disbursement',
        ]);

        $this->assertDatabaseHas('institution_cheques', [
            'cheque_number' => 'CHQ-001',
            'institution_id' => $institution->id,
            'financial_year_id' => $financialYear->id,
            'total_amount' => 4000,
        ]);

        $this->assertCount(2, $cheque->applicants);
        $this->assertDatabaseHas('applicant_institution_cheque', [
            'institution_cheque_id' => $cheque->id,
            'applicant_id' => $firstApplicant->id,
        ]);
        $this->assertDatabaseHas('applicant_institution_cheque', [
            'institution_cheque_id' => $cheque->id,
            'applicant_id' => $secondApplicant->id,
        ]);
    }

    public function test_it_rejects_non_beneficiaries_from_cheque_assignment(): void
    {
        $this->expectException(ValidationException::class);

        $service = app(InstitutionChequeService::class);
        [$institution, $financialYear] = $this->makeInstitutionContext();

        $applicant = $this->makeApplicant($institution, $financialYear, [
            'application_number' => 'APP-003',
            'amount_awarded' => 0,
        ]);

        $service->createForApplicants(new EloquentCollection([$applicant]), [
            'cheque_number' => 'CHQ-002',
            'cheque_date' => now()->toDateString(),
        ]);
    }

    /**
     * @return array{0: Institution, 1: FinancialYear}
     */
    protected function makeInstitutionContext(): array
    {
        $ward = Ward::factory()->create();

        $financialYear = FinancialYear::create([
            'ward_id' => $ward->id,
            'name' => 'FY 2026/2027',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
            'opening_balance' => 0,
            'closing_balance' => 0,
            'budget' => 100000,
        ]);

        $category = InstitutionCategory::create([
            'ward_id' => $ward->id,
            'name' => 'Secondary School',
        ]);

        $institution = Institution::create([
            'ward_id' => $ward->id,
            'name' => 'Murigi Girls Secondary School',
            'code' => 'MGS-' . fake()->unique()->numerify('###'),
            'category_id' => $category->id,
        ]);

        return [$institution, $financialYear];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function makeApplicant(Institution $institution, FinancialYear $financialYear, array $attributes = []): Applicant
    {
        return Applicant::create([
            'ward_id' => $institution->ward_id,
            'financial_year_id' => $financialYear->id,
            'institution_id' => $institution->id,
            'application_number' => $attributes['application_number'] ?? 'APP-' . fake()->unique()->numerify('###'),
            'admission_number' => $attributes['admission_number'] ?? fake()->unique()->numerify('ADM###'),
            'first_name' => $attributes['first_name'] ?? 'Jane',
            'last_name' => $attributes['last_name'] ?? 'Doe',
            'other_name' => $attributes['other_name'] ?? null,
            'name' => $attributes['name'] ?? 'Jane Doe',
            'gender' => $attributes['gender'] ?? 'female',
            'email' => $attributes['email'] ?? null,
            'national_id' => $attributes['national_id'] ?? null,
            'orphan_status' => Applicant::ORPHAN_STATUS_NONE,
            'amount_awarded' => $attributes['amount_awarded'] ?? 0,
            'need_assessment' => 0,
            'fee_balance' => 0,
        ]);
    }
}
