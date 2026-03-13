<?php

namespace App\Http\Controllers;

use App\Exports\QuarterlyReportsExport;
use App\Models\FinancialYear;
use App\Models\Ward;
use App\Services\QuarterlyInstitutionLevelReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuarterlyReportExportController extends Controller
{
    public function __invoke(Request $request, Ward $tenant): BinaryFileResponse
    {
        abort_unless($request->user()?->canAccessTenant($tenant), 403);

        $validated = $request->validate([
            'financial_year_id' => ['required', 'integer'],
            'quarter' => ['required', 'integer', 'between:1,4'],
            'opening_balance' => ['nullable', 'numeric'],
            'money_deposited' => ['nullable', 'numeric'],
            'administration_cost' => ['nullable', 'numeric'],
            'bank_charges' => ['nullable', 'numeric'],
        ]);

        $financialYear = FinancialYear::query()
            ->where('ward_id', $tenant->getKey())
            ->findOrFail($validated['financial_year_id']);

        $data = app(QuarterlyInstitutionLevelReportService::class)->buildWorkbookData(
            financialYear: $financialYear,
            quarter: (int) $validated['quarter'],
            inputs: [
                'opening_balance' => (float) ($validated['opening_balance'] ?? 0),
                'money_deposited' => (float) ($validated['money_deposited'] ?? 0),
                'administration_cost' => (float) ($validated['administration_cost'] ?? 0),
                'bank_charges' => (float) ($validated['bank_charges'] ?? 0),
            ],
        );

        $filename = sprintf(
            'Q%s-%s-%s.xlsx',
            $validated['quarter'],
            str($tenant->name)->slug()->value(),
            str($financialYear->name)->slug()->value(),
        );

        return Excel::download(new QuarterlyReportsExport($data), $filename);
    }
}
