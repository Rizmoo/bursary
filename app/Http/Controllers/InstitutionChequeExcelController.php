<?php

namespace App\Http\Controllers;

use App\Exports\InstitutionChequeBeneficiariesExport;
use App\Models\InstitutionCheque;
use App\Models\Ward;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InstitutionChequeExcelController extends Controller
{
    public function __invoke(Request $request, Ward $tenant, InstitutionCheque $institutionCheque): BinaryFileResponse
    {
        abort_unless($request->user()?->canAccessTenant($tenant), 403);
        abort_unless($institutionCheque->ward_id === $tenant->getKey(), 404);

        $institutionCheque->load(['institution', 'financialYear']);

        $filename = sprintf(
            'cheque-%s-beneficiaries.xlsx',
            str($institutionCheque->cheque_number)->slug()->value(),
        );

        return Excel::download(new InstitutionChequeBeneficiariesExport($institutionCheque), $filename);
    }
}
