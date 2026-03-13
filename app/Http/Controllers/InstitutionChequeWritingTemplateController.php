<?php

namespace App\Http\Controllers;

use App\Exports\InstitutionChequeWritingTemplateExport;
use App\Models\Ward;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InstitutionChequeWritingTemplateController extends Controller
{
    public function __invoke(Request $request, Ward $tenant): BinaryFileResponse
    {
        abort_unless($request->user()?->canAccessTenant($tenant), 403);

        $validated = $request->validate([
            'financial_year_id' => ['required', 'integer'],
            'institution_category_id' => ['nullable', 'integer'],
            'need_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'need_max' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'orphans_only' => ['nullable', 'boolean'],
        ]);

        $filename = sprintf(
            'cheque-writing-template-%s-%s.xlsx',
            str($tenant->name)->slug()->value(),
            now()->format('Ymd-His'),
        );

        return Excel::download(
            new InstitutionChequeWritingTemplateExport(
                wardId: $tenant->getKey(),
                financialYearId: (int) $validated['financial_year_id'],
                filters: [
                    'institution_category_id' => $validated['institution_category_id'] ?? null,
                    'need_min' => $validated['need_min'] ?? null,
                    'need_max' => $validated['need_max'] ?? null,
                    'orphans_only' => (bool) ($validated['orphans_only'] ?? false),
                ],
            ),
            $filename,
        );
    }
}
