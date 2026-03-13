<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use App\Models\Ward;
use App\Support\FinancialYearScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SetFinancialYearScopeController extends Controller
{
    public function __invoke(Request $request, Ward $tenant): RedirectResponse
    {
        abort_unless($request->user()?->canAccessTenant($tenant), 403);

        $validated = $request->validate([
            'financial_year_id' => ['required', 'integer'],
        ]);

        $financialYear = FinancialYear::query()
            ->where('ward_id', $tenant->getKey())
            ->findOrFail((int) $validated['financial_year_id']);

        FinancialYearScope::setSelectedId($tenant->getKey(), $financialYear->getKey());

        return back();
    }
}
