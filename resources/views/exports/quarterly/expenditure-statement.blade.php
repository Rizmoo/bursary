<table>
    <tr><td colspan="3" style="font-weight: bold; text-align: center;">{{ strtoupper($data['tenant_name']) }} WARD</td></tr>
    <tr><td colspan="3" style="font-weight: bold; text-align: center;">{{ strtoupper($data['financial_year_name']) }} {{ strtoupper($data['quarter_label']) }} QUARTER BURSARY EXPENDITURE STATEMENT</td></tr>
    <tr><td colspan="3"></td></tr>
    <tr><td>Bank balance as at {{ strtoupper($data['period_start']->format('j F Y')) }}</td><td></td><td>{{ $data['opening_balance'] }}</td></tr>
    <tr><td>Add: Money deposited into account:</td><td></td><td>{{ $data['money_deposited'] }}</td></tr>
    <tr><td>Amount available for payment:</td><td></td><td>{{ $data['amount_available'] }}</td></tr>
    <tr><td>Less: Total expenditure:</td><td></td><td>{{ $data['total_expenditure'] }}</td></tr>
    <tr><td>Bank balance as at {{ strtoupper($data['period_end']->format('j F Y')) }}</td><td></td><td>{{ $data['closing_balance'] }}</td></tr>
    <tr><td colspan="3"></td></tr>
    <tr><td style="font-weight: bold;">EXPENDITURE BREAKDOWN</td><td></td><td></td></tr>
    <tr><td style="font-weight: bold;">CATEGORY</td><td style="font-weight: bold;">NO.</td><td style="font-weight: bold;">AMOUNT</td></tr>
    <tr><td>Administration Cost</td><td>-</td><td>{{ $data['administration_cost'] }}</td></tr>
    <tr><td>Bank Charges</td><td>-</td><td>{{ $data['bank_charges'] }}</td></tr>
    <tr><td>Universities</td><td>{{ $data['category_breakdown']['universities']['beneficiaries'] }}</td><td>{{ $data['category_breakdown']['universities']['total_awarded'] }}</td></tr>
    <tr><td>Tertiary Colleges</td><td>{{ $data['category_breakdown']['tertiary_colleges']['beneficiaries'] }}</td><td>{{ $data['category_breakdown']['tertiary_colleges']['total_awarded'] }}</td></tr>
    <tr><td>Secondary Schools</td><td>{{ $data['category_breakdown']['secondary']['beneficiaries'] }}</td><td>{{ $data['category_breakdown']['secondary']['total_awarded'] }}</td></tr>
    <tr><td style="font-weight: bold;">TOTAL</td><td style="font-weight: bold;">{{ $data['totals']['beneficiaries'] }}</td><td style="font-weight: bold;">{{ $data['total_expenditure'] }}</td></tr>
</table>
