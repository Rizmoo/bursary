<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Models\Institution;
use App\Models\InstitutionCategory;
use App\Models\InstitutionCheque;
use App\Models\Ward;
use App\Services\InstitutionChequeLifecycleService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InstitutionChequeLifecycleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returning_a_stale_cheque_updates_unutilised_amount(): void
    {
        $service = app(InstitutionChequeLifecycleService::class);
        [$financialYear, $institution] = $this->makeContext();
        $cheque = $this->makeCheque($financialYear, $institution, [
            'status' => InstitutionCheque::STATUS_PENDING,
            'cheque_date' => now()->subMonths(7)->toDateString(),
            'total_amount' => 5000,
        ]);

        $service->returnToUnutilised($cheque);

        $this->assertDatabaseHas('institution_cheques', [
            'id' => $cheque->id,
            'status' => InstitutionCheque::STATUS_RETURNED,
            'returned_amount' => 5000,
        ]);

        $this->assertEquals(5000.0, (float) $financialYear->fresh()->unutilised_amount);
    }

    public function test_marking_a_recent_cheque_as_stale_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $service = app(InstitutionChequeLifecycleService::class);
        [$financialYear, $institution] = $this->makeContext();
        $cheque = $this->makeCheque($financialYear, $institution, [
            'status' => InstitutionCheque::STATUS_PENDING,
            'cheque_date' => now()->subMonths(2)->toDateString(),
        ]);

        $service->markAsStale($cheque);
    }

    /**
     * @return array{0: FinancialYear, 1: Institution}
     */
    protected function makeContext(): array
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
            'unutilised_amount' => 0,
            'budget' => 100000,
        ]);

        $category = InstitutionCategory::create([
            'ward_id' => $ward->id,
            'name' => 'Secondary',
        ]);

        $institution = Institution::create([
            'ward_id' => $ward->id,
            'name' => 'Murigi Girls',
            'code' => 'MGS-' . fake()->unique()->numerify('###'),
            'category_id' => $category->id,
        ]);

        return [$financialYear, $institution];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function makeCheque(FinancialYear $financialYear, Institution $institution, array $attributes = []): InstitutionCheque
    {
        $cheque = InstitutionCheque::create([
            'ward_id' => $institution->ward_id,
            'institution_id' => $institution->id,
            'financial_year_id' => $financialYear->id,
            'cheque_number' => $attributes['cheque_number'] ?? 'CHQ-' . fake()->unique()->numerify('###'),
            'cheque_date' => $attributes['cheque_date'] ?? now()->toDateString(),
            'status' => $attributes['status'] ?? InstitutionCheque::STATUS_PENDING,
            'total_amount' => $attributes['total_amount'] ?? 1000,
            'returned_amount' => $attributes['returned_amount'] ?? 0,
            'remarks' => $attributes['remarks'] ?? null,
        ]);

        $applicant = Applicant::create([
            'ward_id' => $institution->ward_id,
            'financial_year_id' => $financialYear->id,
            'institution_id' => $institution->id,
            'application_number' => 'APP-' . fake()->unique()->numerify('###'),
            'admission_number' => fake()->unique()->numerify('ADM###'),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'name' => 'Jane Doe',
            'gender' => 'female',
            'orphan_status' => Applicant::ORPHAN_STATUS_NONE,
            'amount_awarded' => $attributes['total_amount'] ?? 1000,
            'awarded_at' => now()->subMonths(7)->toDateString(),
            'need_assessment' => 0,
            'fee_balance' => 0,
        ]);

        $cheque->applicants()->attach((new EloquentCollection([$applicant]))->modelKeys());

        return $cheque;
    }
}
