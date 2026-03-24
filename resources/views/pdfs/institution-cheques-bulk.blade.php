<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cheque Beneficiaries</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: auto; }
        .title { text-align: center; margin-bottom: 16px; }
        .title h1, .title h2, .title p { margin: 0; }
        .meta { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta td { padding: 6px 8px; border: 1px solid #d1d5db; }
        table.beneficiaries { width: 100%; border-collapse: collapse; }
        table.beneficiaries th, table.beneficiaries td { border: 1px solid #d1d5db; padding: 8px; }
        table.beneficiaries th { background: #f3f4f6; text-align: left; }
        .text-right { text-align: right; }
        .footer { margin-top: 18px; font-size: 11px; }
    </style>
</head>
<body>
    @foreach($cheques as $cheque)
    <div class="page-break">
        <div class="title">
            <h1>County Government of Kirinyaga</h1>
            <h2>Institution Cheque Beneficiaries</h2>
            <h2>{{ strtoupper($tenant?->name ?? 'ALL') }} WARD</h2>
        </div>

        <table class="meta">
            <tr>
                <td><strong>Institution</strong><br>{{ $cheque->institution->name }}</td>
                <td><strong>Financial Year</strong><br>{{ $cheque->financialYear->name }}</td>
            </tr>
            <tr>
                <td><strong>Cheque Number</strong><br>{{ $cheque->cheque_number }}</td>
                <td><strong>Cheque Date</strong><br>{{ optional($cheque->cheque_date)->format('d M Y') }}</td>
            </tr>
        </table>

        <table class="beneficiaries">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Admission No.</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cheque->applicants as $index => $applicant)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ trim(collect([$applicant->first_name, $applicant->other_name, $applicant->last_name])->filter()->implode(' ')) }}</td>
                        <td>{{ $applicant->admission_number }}</td>
                        <td class="text-right">KES {{ number_format((float) $applicant->amount_awarded, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td class="text-right"><strong>KES {{ number_format((float) $cheque->total_amount, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Generated on {{ now()->format('d M Y H:i') }}.
        </div>
    </div>
    @endforeach
</body>
</html>
