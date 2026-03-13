<?php

namespace App\Http\Controllers;

use App\Exports\ApplicantsBeneficiariesExport;
use App\Exports\ApplicantsExport;
use App\Models\Ward;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApplicantsFilteredExcelController extends Controller
{
    public function __invoke(Request $request, Ward $tenant): BinaryFileResponse
    {
        abort_unless($request->user()?->canAccessTenant($tenant), 403);

        $validated = $request->validate([
            'export_type' => ['required', 'in:applicants,beneficiaries'],
            'scope' => ['nullable', 'in:all,filtered'],
            'financial_year_id' => ['nullable', 'integer'],
            'institution_category_id' => ['nullable', 'integer'],
            'need_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'need_max' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'orphans_only' => ['nullable', 'boolean'],
            'has_disability_filter' => ['nullable', 'in:any,yes,no'],
            'has_disabled_parent_filter' => ['nullable', 'in:any,yes,no'],
            'beneficiaries_only' => ['nullable', 'boolean'],
        ]);

        $scope = $validated['scope'] ?? 'all';

        $filters = [
            'financial_year_id' => $scope === 'filtered' ? ($validated['financial_year_id'] ?? null) : null,
            'institution_category_id' => $scope === 'filtered' ? ($validated['institution_category_id'] ?? null) : null,
            'need_min' => $scope === 'filtered' ? ($validated['need_min'] ?? null) : null,
            'need_max' => $scope === 'filtered' ? ($validated['need_max'] ?? null) : null,
            'orphans_only' => $scope === 'filtered' ? (bool) ($validated['orphans_only'] ?? false) : false,
            'has_disability_filter' => $scope === 'filtered' ? ($validated['has_disability_filter'] ?? 'any') : 'any',
            'has_disabled_parent_filter' => $scope === 'filtered' ? ($validated['has_disabled_parent_filter'] ?? 'any') : 'any',
            'beneficiaries_only' => $scope === 'filtered' ? (bool) ($validated['beneficiaries_only'] ?? false) : false,
        ];

        if ($validated['export_type'] === 'beneficiaries') {
            $filename = sprintf(
                'beneficiaries-%s-%s.xlsx',
                str($tenant->name)->slug()->value(),
                now()->format('Ymd-His'),
            );

            return Excel::download(
                new ApplicantsBeneficiariesExport($tenant->getKey(), [], $tenant->name, $filters),
                $filename,
            );
        }

        $filename = sprintf(
            'applicants-%s-%s.xlsx',
            str($tenant->name)->slug()->value(),
            now()->format('Ymd-His'),
        );

        return Excel::download(
            new ApplicantsExport($tenant->getKey(), [], $filters),
            $filename,
        );
    }
}
