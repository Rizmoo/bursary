<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Ward;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AwardHelperCalculatorService
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function preview(Ward $ward, array $data): array
    {
        $availableAmount = (float) ($data['available_amount'] ?? 0);
        $financialYearId = (int) ($data['financial_year_id'] ?? 0);

        if ($availableAmount <= 0) {
            return $this->emptyPreview();
        }

        if ($financialYearId <= 0) {
            return $this->emptyPreview();
        }

        $applicants = $this->buildEligibleQuery($ward, $data)->get();
        if ($applicants->isEmpty()) {
            return $this->emptyPreview();
        }

        $weights = $this->extractWeights($data);

        $weighted = $applicants->map(function (Applicant $applicant) use ($weights): array {
            $categoryKey = $this->categoryKey($applicant?->institution?->category?->name);
            $categoryWeight = $weights[$categoryKey] ?? $weights['other'];

            $need = max(0, (int) $applicant->need_assessment);
            $needFactor = max(0.2, $need / 100);
            $weight = $categoryWeight * $needFactor;

            return [
                'id' => $applicant->id,
                'category' => $categoryKey,
                'need' => $need,
                'weight' => $weight,
            ];
        });

        $totalWeight = (float) $weighted->sum('weight');

        if ($totalWeight <= 0) {
            return $this->emptyPreview();
        }

        $awards = $weighted->map(function (array $row) use ($availableAmount, $totalWeight): array {
            $amount = round(($availableAmount * (float) $row['weight']) / $totalWeight, 2);

            return [
                ...$row,
                'amount' => $amount,
            ];
        });

        $awardedTotal = (float) $awards->sum('amount');
        $remaining = round($availableAmount - $awardedTotal, 2);

        if (abs($remaining) >= 0.01) {
            $index = $awards->keys()->last();
            if ($index !== null) {
                $last = $awards->get($index);

                if (is_array($last)) {
                    $current = (float) ($last['amount'] ?? 0);
                    $last['amount'] = round($current + $remaining, 2);
                    $awards = $awards->put($index, $last);
                }
            }
        }

        $awardedTotal = (float) $awards->sum('amount');
        $remaining = round($availableAmount - $awardedTotal, 2);

        $matrix = collect(['university', 'tertiary', 'boarding', 'day', 'other'])
            ->mapWithKeys(function (string $key) use ($awards): array {
                $rows = $awards->where('category', $key);

                return [
                    $key => [
                        'count' => $rows->count(),
                        'amount' => round((float) $rows->sum('amount'), 2),
                        'avg' => $rows->count() > 0 ? round(((float) $rows->sum('amount')) / $rows->count(), 2) : 0,
                    ],
                ];
            })
            ->all();

        return [
            'eligible_count' => $applicants->count(),
            'awarded_total' => round($awardedTotal, 2),
            'remaining' => $remaining,
            'matrix' => $matrix,
            'awards' => $awards->mapWithKeys(fn (array $row): array => [$row['id'] => $row['amount']])->all(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function apply(Ward $ward, array $data): array
    {
        $preview = $this->preview($ward, $data);

        if (($preview['eligible_count'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'available_amount' => 'No eligible applicants found for the current settings.',
            ]);
        }

        $awards = Arr::get($preview, 'awards', []);

        DB::transaction(function () use ($awards): void {
            $now = now();

            foreach ($awards as $applicantId => $amount) {
                Applicant::query()
                    ->whereKey((int) $applicantId)
                    ->update([
                        'amount_awarded' => (float) $amount,
                        'awarded_at' => $now->toDateString(),
                        'updated_at' => $now,
                    ]);
            }
        });

        return $preview;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function buildEligibleQuery(Ward $ward, array $data)
    {
        $query = Applicant::query()
            ->with('institution.category')
            ->where('ward_id', $ward->getKey())
            ->where('financial_year_id', (int) $data['financial_year_id'])
            ->whereNotNull('admission_number')
            ->where('admission_number', '!=', '')
            ->where('need_assessment', '>=', (int) ($data['min_need_assessment'] ?? 0));

        if (! empty($data['exclude_cheque_assigned'])) {
            $query->whereDoesntHave('institutionCheques');
        }

        if (empty($data['overwrite_existing_awards'])) {
            $query->where('amount_awarded', '<=', 0);
        }

        if (! empty($data['orphans_only'])) {
            $query->whereIn('orphan_status', [Applicant::ORPHAN_STATUS_PARTIAL, Applicant::ORPHAN_STATUS_TOTAL]);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, float>
     */
    protected function extractWeights(array $data): array
    {
        return [
            'university' => max(0.1, (float) ($data['weight_university'] ?? 1.6)),
            'tertiary' => max(0.1, (float) ($data['weight_tertiary'] ?? 1.35)),
            'boarding' => max(0.1, (float) ($data['weight_boarding'] ?? 1.1)),
            'day' => max(0.1, (float) ($data['weight_day'] ?? 0.8)),
            'other' => max(0.1, (float) ($data['weight_other'] ?? 1.0)),
        ];
    }

    protected function categoryKey(?string $name): string
    {
        $value = strtolower(trim((string) $name));

        if (str_contains($value, 'university')) {
            return 'university';
        }

        if (str_contains($value, 'tertiary')) {
            return 'tertiary';
        }

        if (str_contains($value, 'boarding')) {
            return 'boarding';
        }

        if (str_contains($value, 'day')) {
            return 'day';
        }

        return 'other';
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyPreview(): array
    {
        return [
            'eligible_count' => 0,
            'awarded_total' => 0,
            'remaining' => 0,
            'matrix' => [
                'university' => ['count' => 0, 'amount' => 0, 'avg' => 0],
                'tertiary' => ['count' => 0, 'amount' => 0, 'avg' => 0],
                'boarding' => ['count' => 0, 'amount' => 0, 'avg' => 0],
                'day' => ['count' => 0, 'amount' => 0, 'avg' => 0],
                'other' => ['count' => 0, 'amount' => 0, 'avg' => 0],
            ],
            'awards' => [],
        ];
    }
}
