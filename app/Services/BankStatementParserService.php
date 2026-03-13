<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Smalot\PdfParser\Parser;
use Symfony\Component\Process\Process;

class BankStatementParserService
{
    /**
     * Parse a KCB bank statement PDF and return structured data.
     *
     * @return array{
     *     account_number: ?string,
     *     account_name: ?string,
     *     period_start: ?string,
     *     period_end: ?string,
     *     opening_balance: float,
     *     closing_balance: float,
     *     transactions: array<int, array{
     *         date: ?string,
     *         description: string,
     *         value_date: ?string,
     *         money_out: float,
     *         money_in: float,
     *         ledger_balance: float,
     *         type: string,
     *         cheque_number: ?string,
     *     }>
     * }
     */
    public function parse(string $filePath): array
    {
        $text = $this->extractTextFromPdf($filePath);

        // Normalise whitespace while preserving line breaks
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);

        $lines = array_values(array_filter(
            explode("\n", $text),
            fn (string $line): bool => trim($line) !== ''
        ));

        $rawText = implode("\n", $lines);

        $header = $this->parseHeader($rawText);
        $transactions = $this->parseTransactions($lines);

        return [
            'account_number' => $header['account_number'],
            'account_name' => $header['account_name'],
            'period_start' => $header['period_start'],
            'period_end' => $header['period_end'],
            'opening_balance' => $header['opening_balance'],
            'closing_balance' => $header['closing_balance'],
            'transactions' => $transactions,
        ];
    }

    /**
     * Extract text from PDF using Smalot first, then fallback to pdftotext for PDFs
     * that are flagged as secured by metadata but open without a password.
     */
    protected function extractTextFromPdf(string $filePath): string
    {
        $parserError = null;

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            if (filled(trim($text))) {
                return $text;
            }
        } catch (\Throwable $e) {
            $parserError = $e;
        }

        // Fallback: poppler's pdftotext handles many "secured" PDFs with no user password.
        if ($this->isPdftotextAvailable()) {
            $process = new Process(['pdftotext', '-layout', '-nopgbrk', $filePath, '-']);
            $process->run();

            if ($process->isSuccessful() && filled(trim($process->getOutput()))) {
                return $process->getOutput();
            }
        }

        if ($parserError && str_contains(strtolower($parserError->getMessage()), 'secured pdf file')) {
            throw new \RuntimeException('This PDF appears to be encrypted with document restrictions. It can open normally, but parser libraries may still mark it as secured. Please re-export as "Print to PDF" and upload again.');
        }

        if ($parserError) {
            throw $parserError;
        }

        throw new \RuntimeException('Unable to extract text from the uploaded PDF.');
    }

    protected function isPdftotextAvailable(): bool
    {
        $process = new Process(['sh', '-lc', 'command -v pdftotext']);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Extract header information from the statement text.
     */
    protected function parseHeader(string $text): array
    {
        $accountNumber = null;
        $accountName = null;
        $periodStart = null;
        $periodEnd = null;
        $openingBalance = 0.0;
        $closingBalance = 0.0;

        // Account number: "Account: 1205585958"
        if (preg_match('/Account:\s*([\d]+)/i', $text, $m)) {
            $accountNumber = $m[1];
        }

        // Account name: text between account number and "Current Account"
        if (preg_match('/Account:\s*[\d]+\s+(.+?)(?:Current\s*Account|$)/i', $text, $m)) {
            $accountName = trim($m[1]);
        }

        // Statement Period: "Statement Period: 01 SEP 2025 - 30 SEP 2025"
        if (preg_match('/Statement\s*Period:\s*(\d{1,2}\s+\w+\s+\d{4})\s*-\s*(\d{1,2}\s+\w+\s+\d{4})/i', $text, $m)) {
            $periodStart = $this->parseDate($m[1]);
            $periodEnd = $this->parseDate($m[2]);
        }

        // Balance at Period Start
        if (preg_match('/Balance\s*at\s*Period\s*Start\s*([\d,]+\.\d{2})/i', $text, $m)) {
            $openingBalance = $this->parseAmount($m[1]);
        }

        // Balance at Period End
        if (preg_match('/Balance\s*at\s*Period\s*End:\s*([\d,]+\.\d{2})/i', $text, $m)) {
            $closingBalance = $this->parseAmount($m[1]);
        }

        return [
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }

    /**
     * Parse transaction rows from the statement lines.
     */
    protected function parseTransactions(array $lines): array
    {
        $transactions = [];
        $inTransactionBlock = false;

        // We need to find the transaction block between the header row and the end
        // Header row is "TXN DATE DESCRIPTION VALUE DATE MONEY OUT MONEY IN LEDGER BALANCE"
        foreach ($lines as $i => $line) {
            if (preg_match('/TXN\s*DATE/i', $line)) {
                $inTransactionBlock = true;

                continue;
            }

            if (! $inTransactionBlock) {
                continue;
            }

            // Skip separator rows
            if (preg_match('/^[\s=]+$/', $line)) {
                continue;
            }

            // Try to parse as a transaction line starting with a date
            $txn = $this->parseTransactionLine($line, $lines, $i);
            if ($txn !== null) {
                $transactions[] = $txn;
            }
        }

        // Classify each transaction
        return array_map(fn (array $txn): array => $this->classifyTransaction($txn), $transactions);
    }

    /**
     * Try to parse a single transaction line.
     */
    protected function parseTransactionLine(string $line, array $allLines, int $index): ?array
    {
        // Transaction lines start with a date: "01 SEP 2025" or "08 SEP 2025"
        // But also "BALANCE AT PERIOD END:" has no leading date
        $datePattern = '(\d{1,2}\s+\w{3}\s+\d{4})';

        // Try matching a line with: TXN_DATE DESCRIPTION VALUE_DATE MONEY_OUT MONEY_IN LEDGER_BALANCE
        // The amounts might be negative like -3,000.00
        $amountPattern = '(-?[\d,]+\.\d{2})';

        // Pattern 1: Full transaction line with date
        if (preg_match('/^'.$datePattern.'\s+(.+?)\s+'.$datePattern.'\s+'.$amountPattern.'\s+'.$amountPattern.'\s*$/i', $line, $m)) {
            // Has all 5 fields: date, desc, value_date, money_out/in (2 amounts), no ledger
            // This is unlikely, let's try another pattern
        }

        // The PDF text may have amounts spread across columns.
        // Let's try a more flexible approach: parse lines that start with a date

        // Match: DATE  DESCRIPTION  VALUE_DATE  [AMOUNT]  [AMOUNT]  [AMOUNT]
        // Amounts can appear in money_out, money_in, or ledger_balance columns

        // First check if line starts with a date
        if (preg_match('/^\s*'.$datePattern.'/i', $line, $dateMatch)) {
            $date = $this->parseDate($dateMatch[1]);
            $rest = trim(substr($line, strlen($dateMatch[0])));

            return $this->extractTransactionFields($date, $rest);
        }

        // "BALANCE AT PERIOD E ND:" line (no leading date)
        if (preg_match('/BALANCE\s*AT\s*PERIOD/i', $line)) {
            $description = 'BALANCE AT PERIOD END';
            $amounts = $this->extractAmounts($line);

            return [
                'date' => null,
                'description' => $description,
                'value_date' => null,
                'money_out' => $amounts['money_out'],
                'money_in' => $amounts['money_in'],
                'ledger_balance' => $amounts['ledger_balance'],
                'type' => 'balance_end',
                'cheque_number' => null,
            ];
        }

        return null;
    }

    /**
     * Extract transaction fields from the rest of a line (after the txn date).
     */
    protected function extractTransactionFields(string $date, string $rest): array
    {
        $datePattern = '(\d{1,2}\s+\w{3}\s+\d{4})';

        // Try to find a value date in the rest
        $valueDate = null;
        $description = '';
        $amounts = ['money_out' => 0.0, 'money_in' => 0.0, 'ledger_balance' => 0.0];

        if (preg_match('/^(.+?)\s+'.$datePattern.'\s+(.*?)$/i', $rest, $m)) {
            $description = trim($m[1]);
            $valueDate = $this->parseDate($m[2]);
            $amountStr = trim($m[3]);

            $amounts = $this->parseAmountColumns($amountStr);
        } else {
            // No value date found, treat entire rest as description + amounts
            $description = $rest;
            $amounts = $this->extractAmounts($rest);
        }

        // Clean up multi-line descriptions (remove trailing numbers from description)
        $description = preg_replace('/\s+(-?[\d,]+\.\d{2})/', '', $description);
        $description = trim($description);

        return [
            'date' => $date,
            'description' => $description,
            'value_date' => $valueDate,
            'money_out' => $amounts['money_out'],
            'money_in' => $amounts['money_in'],
            'ledger_balance' => $amounts['ledger_balance'],
            'type' => 'other',
            'cheque_number' => null,
        ];
    }

    /**
     * Parse amounts from a string that may contain 1-3 amount columns.
     * Columns are: MONEY OUT | MONEY IN | LEDGER BALANCE
     */
    protected function parseAmountColumns(string $str): array
    {
        $amounts = [];
        preg_match_all('/(-?[\d,]+\.\d{2})/', $str, $matches);

        if (! empty($matches[1])) {
            $amounts = array_map(fn (string $a): float => $this->parseAmount($a), $matches[1]);
        }

        // Determine which column each amount belongs to based on count:
        // 1 amount = ledger_balance only (BALANCE B/FWD)
        // 2 amounts = money_out + ledger_balance OR money_in + ledger_balance
        // 3 amounts = money_out + money_in + ledger_balance

        $moneyOut = 0.0;
        $moneyIn = 0.0;
        $ledgerBalance = 0.0;

        $count = count($amounts);
        if ($count === 1) {
            $ledgerBalance = $amounts[0];
        } elseif ($count === 2) {
            // If first amount is negative, it's money_out
            if ($amounts[0] < 0) {
                $moneyOut = abs($amounts[0]);
                $ledgerBalance = $amounts[1];
            } else {
                // Could be money_in + ledger, or money_out (positive withdrawal?) + ledger
                // Based on KCB format, money_out is shown as negative
                $moneyIn = $amounts[0];
                $ledgerBalance = $amounts[1];
            }
        } elseif ($count >= 3) {
            $moneyOut = abs($amounts[0]);
            $moneyIn = $amounts[1];
            $ledgerBalance = $amounts[2];
        }

        return [
            'money_out' => $moneyOut,
            'money_in' => $moneyIn,
            'ledger_balance' => $ledgerBalance,
        ];
    }

    /**
     * Extract amounts from a text line.
     */
    protected function extractAmounts(string $text): array
    {
        preg_match_all('/(-?[\d,]+\.\d{2})/', $text, $matches);

        $amounts = array_map(fn (string $a): float => $this->parseAmount($a), $matches[1] ?? []);

        return $this->parseAmountColumns(implode(' ', $matches[0] ?? []));
    }

    /**
     * Classify a parsed transaction by its description.
     */
    protected function classifyTransaction(array $txn): array
    {
        $desc = strtoupper($txn['description']);

        // Balance B/FWD
        if (str_contains($desc, 'BALANCE B/FWD') || str_contains($desc, 'BALANCE BF')) {
            $txn['type'] = 'balance_bfwd';

            return $txn;
        }

        // Balance at period end
        if (str_contains($desc, 'BALANCE AT PERIOD')) {
            $txn['type'] = 'balance_end';

            return $txn;
        }

        // Cheque cleared: "Inward Cheque D CHQ2616" or "INHouse CHQ002974"
        // First extract cheque number
        $chequeNumber = $this->extractChequeNumber($desc);
        if ($chequeNumber !== null) {
            $txn['cheque_number'] = $chequeNumber;
        }

        // Bounced / Unpaid reversal: "INHouse Unpaid 2974" (money comes back IN)
        if (preg_match('/UNPAID|DISHONO/i', $desc) && $txn['money_in'] > 0) {
            $txn['type'] = 'bounced_reversal';
            if ($chequeNumber === null) {
                $txn['cheque_number'] = $this->extractNumberFromDesc($desc);
            }

            return $txn;
        }

        // Penalty / Unpaid item charge: "Unpaid Item charge"
        if (preg_match('/UNPAID\s*ITEM\s*CHA/i', $desc)) {
            $txn['type'] = 'penalty';
            if ($chequeNumber === null) {
                $txn['cheque_number'] = $this->extractNumberFromDesc($desc);
            }

            return $txn;
        }

        // Cheque presented and cleared: "Inward Cheque" or "INHouse CHQ"
        if ($chequeNumber !== null && $txn['money_out'] > 0) {
            $txn['type'] = 'cheque_cleared';

            return $txn;
        }

        // Deposits (money in, not a reversal)
        if ($txn['money_in'] > 0) {
            $txn['type'] = 'deposit';

            return $txn;
        }

        // Other charges
        if ($txn['money_out'] > 0 && preg_match('/CHARGE|FEE|COMM|LEVY|EXCISE|WHT/i', $desc)) {
            $txn['type'] = 'bank_charge';

            return $txn;
        }

        return $txn;
    }

    /**
     * Extract cheque number from a transaction description.
     *
     * Known patterns:
     * - "Inward Cheque D CHQ2616" → 2616
     * - "INHouse CHQ002974" → 002974
     * - "CHQ-2974" → 2974
     */
    protected function extractChequeNumber(string $desc): ?string
    {
        // CHQ followed by optional separators then digits
        if (preg_match('/CHQ[:\-\s]*0*(\d+)/i', $desc, $m)) {
            return $m[1];
        }

        // "Cheque" followed by something then a number
        if (preg_match('/CHEQUE\s+\w?\s*(\d+)/i', $desc, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Extract any number from the description as a fallback for cheque number.
     */
    protected function extractNumberFromDesc(string $desc): ?string
    {
        // Look for CHQ pattern first
        if (preg_match('/CHQ[:\-\s]*0*(\d+)/i', $desc, $m)) {
            return $m[1];
        }

        // Look for standalone 3-6 digit numbers
        if (preg_match('/\b(\d{3,6})\b/', $desc, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Parse a date string like "01 SEP 2025" to Y-m-d format.
     */
    protected function parseDate(string $dateStr): ?string
    {
        try {
            return Carbon::createFromFormat('d M Y', trim($dateStr))?->format('Y-m-d');
        } catch (\Throwable) {
            try {
                return Carbon::parse(trim($dateStr))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
    }

    /**
     * Parse a formatted amount string like "3,000.00" or "-3,000.00" to float.
     */
    protected function parseAmount(string $amount): float
    {
        $cleaned = str_replace(',', '', trim($amount));

        return (float) $cleaned;
    }
}
