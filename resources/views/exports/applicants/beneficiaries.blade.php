<table>
    <tr>
        <td>COUNTY GOVERNMENT OF KIRINYAGA</td>
        <td></td><td></td><td></td><td></td>
    </tr>
    <tr>
        <td>FY {{ strtoupper($financialYearLabel) }} EDUCATION BURSARY FUND</td>
        <td></td><td></td><td></td><td></td>
    </tr>
    <tr>
        <td>LIST OF BENEFICIARIES</td>
        <td></td><td></td><td></td><td></td>
    </tr>
    <tr>
        <td>{{ strtoupper($wardName) }} WARD</td>
        <td></td><td></td><td></td><td></td>
    </tr>
    <tr>
        <th>ADM. NO.</th>
        <th>NAME OF BENEFICIARY</th>
        <th>GENDER</th>
        <th>NAME OF INSTITUTION</th>
        <th>Amount</th>
    </tr>
    @foreach ($rows as $row)
        <tr>
            <td>{{ $row->admission_number }}</td>
            <td>{{ trim(collect([$row->first_name, $row->other_name, $row->last_name])->filter()->implode(' ')) }}</td>
            <td>{{ strtoupper((string) $row->gender) }}</td>
            <td>{{ strtoupper((string) $row->institution?->name) }}</td>
            <td>{{ (float) $row->amount_awarded }}</td>
        </tr>
    @endforeach
</table>
