<?php

namespace App\Support;

use App\Models\FinancialYear;

class FinancialYearScope
{
    public static function sessionKey(int $tenantId): string
    {
        return "financial_year_scope.{$tenantId}";
    }

    public static function getSelectedId(?int $tenantId): ?int
    {
        if (! $tenantId) {
            return null;
        }

        $value = session()->get(self::sessionKey($tenantId));

        return filled($value) ? (int) $value : null;
    }

    public static function setSelectedId(int $tenantId, int $financialYearId): void
    {
        session()->put(self::sessionKey($tenantId), $financialYearId);
    }

    public static function resolveForTenant(?int $tenantId): ?FinancialYear
    {
        if (! $tenantId) {
            return null;
        }

        $selectedId = self::getSelectedId($tenantId);

        if ($selectedId) {
            $selected = FinancialYear::query()
                ->where('ward_id', $tenantId)
                ->find($selectedId);

            if ($selected) {
                return $selected;
            }
        }

        $fallback = FinancialYear::query()
            ->where('ward_id', $tenantId)
            ->where('is_current', true)
            ->latest('start_date')
            ->first()
            ?? FinancialYear::query()
                ->where('ward_id', $tenantId)
                ->latest('start_date')
                ->first();

        if ($fallback) {
            self::setSelectedId($tenantId, $fallback->getKey());
        }

        return $fallback;
    }
}
