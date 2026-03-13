<?php

namespace App\Http\Controllers;

use App\Exports\ApplicantsExport;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApplicantsExcelController extends Controller
{
    public function __invoke(Request $request, Ward $tenant): BinaryFileResponse
    {
        abort_unless($request->user()?->canAccessTenant($tenant), 403);

        $validated = $request->validate([
            'ids' => ['required', 'string'],
        ]);

        $ids = collect(explode(',', $validated['ids']))
            ->map(fn (string $id): int => (int) trim($id))
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw ValidationException::withMessages([
                'ids' => 'No applicants were selected for export.',
            ]);
        }

        $filename = sprintf(
            'applicants-%s-%s.xlsx',
            str($tenant->name)->slug()->value(),
            now()->format('Ymd-His'),
        );

        return Excel::download(new ApplicantsExport($tenant->getKey(), $ids), $filename);
    }
}
