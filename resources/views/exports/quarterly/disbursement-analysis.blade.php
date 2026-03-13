<table>
    <tr><td colspan="14" style="font-weight: bold; text-align: center;">COUNTY GOVERNMENT OF KIRINYAGA</td></tr>
    <tr><td colspan="14" style="font-weight: bold; text-align: center;">{{ strtoupper($data['quarter_label']) }} QUARTER EDUCATION BURSARY FUND REPORT</td></tr>
    <tr><td colspan="14" style="font-weight: bold; text-align: center;">{{ strtoupper($data['period_start']->format('j F Y')) }} - {{ strtoupper($data['period_end']->format('j F Y')) }}</td></tr>
    <tr><td colspan="14" style="font-weight: bold; text-align: center;">BURSARY DISBURSEMENT ANALYSIS</td></tr>
    <tr><td colspan="14"></td></tr>
    <tr>
        <td rowspan="2" style="font-weight: bold;">WARD</td>
        <td colspan="2" style="font-weight: bold; text-align: center;">UNIVERSITIES</td>
        <td colspan="2" style="font-weight: bold; text-align: center;">TERTIARY COLLEGES</td>
        <td colspan="2" style="font-weight: bold; text-align: center;">SECONDARY</td>
        <td colspan="2" style="font-weight: bold; text-align: center;">TOTALS</td>
        <td rowspan="2" style="font-weight: bold;">ADMN COST</td>
        <td rowspan="2" style="font-weight: bold;">TOTAL BANK CHARGES</td>
        <td rowspan="2" style="font-weight: bold;">BANK BAL - {{ strtoupper($data['period_start']->format('j M Y')) }}</td>
        <td rowspan="2" style="font-weight: bold;">MONEY DEPOSITED INTO ACCOUNT</td>
        <td rowspan="2" style="font-weight: bold;">BANK BAL - {{ strtoupper($data['period_end']->format('j M Y')) }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">STUDENTS</td>
        <td style="font-weight: bold;">AMOUNT</td>
        <td style="font-weight: bold;">STUDENTS</td>
        <td style="font-weight: bold;">AMOUNT</td>
        <td style="font-weight: bold;">STUDENTS</td>
        <td style="font-weight: bold;">AMOUNT</td>
        <td style="font-weight: bold;">STUDENTS</td>
        <td style="font-weight: bold;">AMOUNT</td>
    </tr>
    <tr>
        <td>{{ strtoupper($data['tenant_name']) }}</td>
        <td>{{ $data['category_breakdown']['universities']['beneficiaries'] }}</td>
        <td>{{ $data['category_breakdown']['universities']['total_awarded'] }}</td>
        <td>{{ $data['category_breakdown']['tertiary_colleges']['beneficiaries'] }}</td>
        <td>{{ $data['category_breakdown']['tertiary_colleges']['total_awarded'] }}</td>
        <td>{{ $data['category_breakdown']['secondary']['beneficiaries'] }}</td>
        <td>{{ $data['category_breakdown']['secondary']['total_awarded'] }}</td>
        <td>{{ $data['totals']['beneficiaries'] }}</td>
        <td>{{ $data['totals']['total_awarded'] }}</td>
        <td>{{ $data['administration_cost'] }}</td>
        <td>{{ $data['bank_charges'] }}</td>
        <td>{{ $data['opening_balance'] }}</td>
        <td>{{ $data['money_deposited'] }}</td>
        <td>{{ $data['closing_balance'] }}</td>
    </tr>
</table>
