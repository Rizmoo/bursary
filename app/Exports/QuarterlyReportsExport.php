<?php

namespace App\Exports;

use App\Exports\Sheets\QuarterlyDisbursementAnalysisSheet;
use App\Exports\Sheets\QuarterlyExpenditureStatementSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuarterlyReportsExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        protected array $data,
    ) {}

    public function sheets(): array
    {
        return [
            new QuarterlyExpenditureStatementSheet($this->data),
            new QuarterlyDisbursementAnalysisSheet($this->data),
        ];
    }
}
