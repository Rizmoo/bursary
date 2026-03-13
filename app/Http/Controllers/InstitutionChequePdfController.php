<?php

namespace App\Http\Controllers;

use App\Models\InstitutionCheque;
use App\Models\Ward;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstitutionChequePdfController extends Controller
{
    public function __invoke(Request $request, Ward $tenant, InstitutionCheque $institutionCheque): Response
    {
        abort_unless($request->user()?->canAccessTenant($tenant), 403);
        abort_unless($institutionCheque->ward_id === $tenant->getKey(), 404);

        $institutionCheque->load(['institution', 'financialYear', 'applicants' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name')]);

        $pdf = Pdf::loadView('pdfs.institution-cheque-beneficiaries', [
            'cheque' => $institutionCheque,
            'tenant' => $tenant,
        ])->setPaper('a4', 'portrait');

        return $pdf->download(sprintf('cheque-%s-beneficiaries.pdf', str($institutionCheque->cheque_number)->slug()->value()));
    }
}
