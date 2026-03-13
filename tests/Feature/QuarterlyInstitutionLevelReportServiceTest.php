<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Models\Institution;
use App\Models\InstitutionCategory;
use App\Models\Ward;
use App\Services\QuarterlyInstitutionLevelReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuarterlyInstitutionLevelReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_beneficiaries_and_awards_by_institution_level_for_a_quarter(): void
    {
        $service = app(QuarterlyInstitutionLevelReportService::class);
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

        $secondary = InstitutionCategory::create([
            'ward_id' => $ward->id,
            'name' => 'Secondary',
        ]);

        $university = InstitutionCategory::create([
            'ward_id' => $ward->id,
            'name' => 'University',
        ]);

        $secondaryInstitution = Institution::create([
            'ward_id' => $ward->id,
            'name' => 'Murigi Girls',
            'code' => 'SEC-' . fake()->unique()->numerify('###'),
            'category_id' => $secondary->id,
        ]);

        $universityInstitution = Institution::create([
            'ward_id' => $ward->id,
            'name' => 'Kirinyaga University',
            'code' => 'UNI-' . fake()->unique()->numerify('###'),
            'category_id' => $university->id,
        ]);

        $this->makeApplicant($secondaryInstitution, $financialYear, 1200, '2026-02-15');
        $this->makeApplicant($secondaryInstitution, $financialYear, 800, '2026-03-10');
        $this->makeApplicant($universityInstitution, $financialYear, 2500, '2026-02-25');
        $this->makeApplicant($universityInstitution, $financialYear, 500, '2026-07-01');

        $report = $service->generate($financialYear, 1);

        $this->assertEquals(3, $report['totals']['beneficiaries']);
        $this->assertEquals(4500.0, $report['totals']['total_awarded']);
        $this->assertSame('2026-01-01', $report['period_start']->toDateString());
        $this->assertSame('2026-03-31', $report['period_end']->toDateString());

        $secondaryRow = $report['rows']->firstWhere('name', 'Secondary');
        $universityRow = $report['rows']->firstWhere('name', 'University');

        $this->assertSame(2, $secondaryRow['beneficiaries']);
        $this->assertEquals(2000.0, $secondaryRow['total_awarded']);
        $this->assertSame(1, $universityRow['beneficiaries']);
        $this->assertEquals(2500.0, $universityRow['total_awarded']);
    }

    protected function makeApplicant(Institution $institution, FinancialYear $financialYear, float $amountAwarded, string $awardedAt): Applicant
    {
        return Applicant::create([
            'ward_id' => $institution->ward_id,
            'financial_year_id' => $financialYear->id,
            'institution_id' => $institution->id,
            'application_number' => 'APP-' . fake()->unique()->numerify('###'),
            'admission_number' => fake()->unique()->numerify('ADM###'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'gender' => 'male',
            'orphan_status' => Applicant::ORPHAN_STATUS_NONE,
            'amount_awarded' => $amountAwarded,
            'awarded_at' => $awardedAt,
            'need_assessment' => 0,
            'fee_balance' => 0,
        ]);
    }
}
