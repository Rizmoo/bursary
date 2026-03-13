<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Institution;
use App\Models\Ward;
use Illuminate\Support\Collection;

class InstitutionDuplicateMatcherService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function findPotentialDuplicates(Ward $ward, ?int $financialYearId = null): Collection
    {
        $institutions = Institution::query()
            ->where('ward_id', $ward->getKey())
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        if ($institutions->count() < 2) {
            return collect();
        }

        $counts = Applicant::query()
            ->selectRaw('institution_id, count(*) as total')
            ->where('ward_id', $ward->getKey())
            ->when($financialYearId, fn ($query) => $query->where('financial_year_id', $financialYearId))
            ->groupBy('institution_id')
            ->pluck('total', 'institution_id');

        $rows = [];

        $items = $institutions->values();
        $n = $items->count();

        for ($i = 0; $i < $n; $i++) {
            $a = $items[$i];
            for ($j = $i + 1; $j < $n; $j++) {
                $b = $items[$j];

                [$score, $reason] = $this->matchScore((string) $a->name, (string) $b->name, (string) ($a->code ?? ''), (string) ($b->code ?? ''));

                if ($score < 85) {
                    continue;
                }

                $aCount = (int) ($counts[$a->id] ?? 0);
                $bCount = (int) ($counts[$b->id] ?? 0);

                $sourceId = $aCount <= $bCount ? (int) $a->id : (int) $b->id;
                $targetId = $aCount <= $bCount ? (int) $b->id : (int) $a->id;

                $rows[] = [
                    'score' => $score,
                    'reason' => $reason,
                    'source_id' => $sourceId,
                    'target_id' => $targetId,
                    'source_name' => $sourceId === (int) $a->id ? (string) $a->name : (string) $b->name,
                    'target_name' => $targetId === (int) $b->id ? (string) $b->name : (string) $a->name,
                    'source_count' => $sourceId === (int) $a->id ? $aCount : $bCount,
                    'target_count' => $targetId === (int) $b->id ? $bCount : $aCount,
                ];
            }
        }

        return collect($rows)
            ->sortByDesc('score')
            ->values();
    }

    /**
     * @return array{0:int, 1:string}
     */
    protected function matchScore(string $nameA, string $nameB, string $codeA = '', string $codeB = ''): array
    {
        $normA = $this->normalize($nameA);
        $normB = $this->normalize($nameB);

        if ($normA === $normB) {
            return [100, 'Exact normalized name match'];
        }

        similar_text($normA, $normB, $charSimilarity);

        $tokensA = collect(explode(' ', $normA))->filter()->values();
        $tokensB = collect(explode(' ', $normB))->filter()->values();

        $intersection = $tokensA->intersect($tokensB)->count();
        $union = $tokensA->merge($tokensB)->unique()->count();
        $tokenSimilarity = $union > 0 ? ($intersection / $union) * 100 : 0;

        $lev = levenshtein($normA, $normB);

        $score = (int) round(max($charSimilarity, $tokenSimilarity));
        $reason = $charSimilarity >= $tokenSimilarity ? 'High character similarity' : 'High token similarity';

        if ($lev <= 2 && min(strlen($normA), strlen($normB)) >= 8) {
            $score = max($score, 90);
            $reason = 'Very small edit distance';
        }

        if ($this->looksLikeCodeVariant($codeA, $codeB)) {
            $score = min(100, $score + 5);
            $reason .= ' + code variant';
        }

        return [$score, $reason];
    }

    protected function normalize(string $name): string
    {
        $name = strtoupper($name);
        $name = str_replace(['.', ',', '-', '/', '\\'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        $map = [
            ' SEC SCH ' => ' SECONDARY SCHOOL ',
            ' SEC ' => ' SECONDARY ',
            ' SCH ' => ' SCHOOL ',
            ' INST ' => ' INSTITUTE ',
        ];

        $name = ' ' . trim($name) . ' ';
        foreach ($map as $from => $to) {
            $name = str_replace($from, $to, $name);
        }

        return trim(preg_replace('/\s+/', ' ', $name) ?? $name);
    }

    protected function looksLikeCodeVariant(string $codeA, string $codeB): bool
    {
        $a = strtoupper(trim($codeA));
        $b = strtoupper(trim($codeB));

        if ($a === '' || $b === '') {
            return false;
        }

        return str_starts_with($a, $b) || str_starts_with($b, $a);
    }
}
