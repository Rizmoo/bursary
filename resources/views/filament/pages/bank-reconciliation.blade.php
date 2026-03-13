<x-filament-panels::page>
    @php($reconciliation = $this->getReconciliation())
    @php($summary = $this->getSummaryData())

    <div style="display: grid; gap: 1.5rem;">
        {{-- Upload Section --}}
        @if (! $reconciliation || $reconciliation->isApplied())
            <x-filament::section heading="Upload Bank Statement" description="Upload a KCB bank statement PDF to reconcile cheques against the system.">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label class="fi-fo-field-wrp-label">Financial Year</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="financialYearId">
                                <option value="">-- Select --</option>
                                @foreach ($this->getFinancialYearOptions() as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <div style="display: flex; align-items: end; gap: 1rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 250px;">
                        <label class="fi-fo-field-wrp-label">Bank Statement PDF</label>
                        <input type="file" wire:model="statementFile" accept=".pdf" style="display: block; width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem;">
                        @error('statementFile') <span style="color: #dc2626; font-size: 0.8rem;">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-filament::button wire:click="uploadStatement" wire:loading.attr="disabled" icon="heroicon-o-arrow-up-tray">
                            <span wire:loading.remove wire:target="uploadStatement">Upload &amp; Parse</span>
                            <span wire:loading wire:target="uploadStatement">Parsing...</span>
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @endif

        {{-- Reconciliation Results --}}
        @if ($reconciliation && $summary)
            {{-- Statement Info --}}
            <x-filament::section heading="Statement Details" icon="heroicon-o-document-text">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <div>
                        <span style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Account</span>
                        <div style="font-weight: 600;">{{ $reconciliation->account_number ?? '—' }}</div>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Account Name</span>
                        <div style="font-weight: 600;">{{ $reconciliation->account_name ?? '—' }}</div>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Period</span>
                        <div style="font-weight: 600;">
                            {{ $reconciliation->statement_period_start?->format('d M Y') ?? '—' }}
                            —
                            {{ $reconciliation->statement_period_end?->format('d M Y') ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Opening Balance</span>
                        <div style="font-weight: 600;">KES {{ number_format((float) $reconciliation->opening_balance, 2) }}</div>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Closing Balance</span>
                        <div style="font-weight: 600;">KES {{ number_format((float) $reconciliation->closing_balance, 2) }}</div>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: #6b7280; text-transform: uppercase;">Status</span>
                        <div>
                            @if ($reconciliation->isApplied())
                                <x-filament::badge color="success">Applied</x-filament::badge>
                            @else
                                <x-filament::badge color="warning">Draft — Review &amp; Apply</x-filament::badge>
                            @endif
                        </div>
                    </div>
                </div>
            </x-filament::section>

            {{-- Summary Stats --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <x-filament::section heading="Cheques Cleared" compact>
                    <div style="font-size: 1.8rem; font-weight: 700; color: #16a34a;">
                        {{ $reconciliation->total_cheques_cleared }}
                    </div>
                    <div style="font-size: 0.85rem; color: #6b7280;">KES {{ number_format((float) $reconciliation->total_cleared_amount, 2) }}</div>
                </x-filament::section>

                <x-filament::section heading="Cheques Bounced" compact>
                    <div style="font-size: 1.8rem; font-weight: 700; color: #dc2626;">
                        {{ $reconciliation->total_cheques_bounced }}
                    </div>
                    <div style="font-size: 0.85rem; color: #6b7280;">KES {{ number_format((float) $reconciliation->total_bounced_amount, 2) }}</div>
                </x-filament::section>

                <x-filament::section heading="Penalties" compact>
                    <div style="font-size: 1.8rem; font-weight: 700; color: #ea580c;">
                        KES {{ number_format((float) $reconciliation->total_penalties, 2) }}
                    </div>
                </x-filament::section>

                <x-filament::section heading="Bank Charges" compact>
                    <div style="font-size: 1.8rem; font-weight: 700; color: #d97706;">
                        KES {{ number_format((float) $reconciliation->total_bank_charges, 2) }}
                    </div>
                </x-filament::section>

                <x-filament::section heading="Matched" compact>
                    <div style="font-size: 1.8rem; font-weight: 700; color: #2563eb;">
                        {{ $summary['matched_count'] }}
                    </div>
                    <div style="font-size: 0.85rem; color: #6b7280;">of {{ $summary['items']->whereIn('type', ['cheque_cleared', 'bounced_reversal', 'cheque_bounced'])->count() }} cheque transactions</div>
                </x-filament::section>
            </div>

            {{-- Cleared Cheques --}}
            @if ($summary['cleared']->isNotEmpty())
                <x-filament::section heading="Cleared Cheques" icon="heroicon-o-check-circle" collapsible>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                            <thead>
                                <tr style="background: #f0fdf4;">
                                    <th style="text-align: left; border-bottom: 2px solid #bbf7d0; padding: 0.6rem;">Date</th>
                                    <th style="text-align: left; border-bottom: 2px solid #bbf7d0; padding: 0.6rem;">Cheque #</th>
                                    <th style="text-align: left; border-bottom: 2px solid #bbf7d0; padding: 0.6rem;">Description</th>
                                    <th style="text-align: right; border-bottom: 2px solid #bbf7d0; padding: 0.6rem;">Amount</th>
                                    <th style="text-align: left; border-bottom: 2px solid #bbf7d0; padding: 0.6rem;">System Match</th>
                                    <th style="text-align: left; border-bottom: 2px solid #bbf7d0; padding: 0.6rem;">Institution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary['cleared'] as $item)
                                    <tr>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">{{ $item->transaction_date?->format('d M Y') }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; font-weight: 600;">{{ $item->cheque_number ?? '—' }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; max-width: 280px; word-wrap: break-word;">{{ $item->description }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; text-align: right;">KES {{ number_format((float) $item->money_out, 2) }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">
                                            @if ($item->is_matched)
                                                <x-filament::badge color="success" size="sm">Matched</x-filament::badge>
                                            @else
                                                <x-filament::badge color="danger" size="sm">Unmatched</x-filament::badge>
                                            @endif
                                        </td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">{{ $item->institutionCheque?->institution?->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            @endif

            {{-- Bounced Cheques --}}
            @if ($summary['bounced']->isNotEmpty())
                <x-filament::section heading="Bounced / Returned Cheques" icon="heroicon-o-x-circle" collapsible>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                            <thead>
                                <tr style="background: #fef2f2;">
                                    <th style="text-align: left; border-bottom: 2px solid #fecaca; padding: 0.6rem;">Date</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fecaca; padding: 0.6rem;">Cheque #</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fecaca; padding: 0.6rem;">Description</th>
                                    <th style="text-align: right; border-bottom: 2px solid #fecaca; padding: 0.6rem;">Money Out</th>
                                    <th style="text-align: right; border-bottom: 2px solid #fecaca; padding: 0.6rem;">Money In</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fecaca; padding: 0.6rem;">System Match</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fecaca; padding: 0.6rem;">Institution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary['bounced'] as $item)
                                    <tr>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">{{ $item->transaction_date?->format('d M Y') }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; font-weight: 600;">{{ $item->cheque_number ?? '—' }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; max-width: 280px; word-wrap: break-word;">{{ $item->description }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $item->money_out > 0 ? 'KES ' . number_format((float) $item->money_out, 2) : '—' }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $item->money_in > 0 ? 'KES ' . number_format((float) $item->money_in, 2) : '—' }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">
                                            @if ($item->is_matched)
                                                <x-filament::badge color="success" size="sm">Matched</x-filament::badge>
                                            @else
                                                <x-filament::badge color="danger" size="sm">Unmatched</x-filament::badge>
                                            @endif
                                        </td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">{{ $item->institutionCheque?->institution?->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            @endif

            {{-- Penalties & Charges --}}
            @if ($summary['penalties']->isNotEmpty() || $summary['bank_charges']->isNotEmpty())
                <x-filament::section heading="Penalties &amp; Bank Charges" icon="heroicon-o-exclamation-triangle" collapsible>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                            <thead>
                                <tr style="background: #fffbeb;">
                                    <th style="text-align: left; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Date</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Type</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Description</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Cheque #</th>
                                    <th style="text-align: right; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary['penalties']->merge($summary['bank_charges']) as $item)
                                    <tr>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">{{ $item->transaction_date?->format('d M Y') }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">
                                            <x-filament::badge :color="$item->getTypeColor()" size="sm">{{ $item->getTypeLabel() }}</x-filament::badge>
                                        </td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; max-width: 300px; word-wrap: break-word;">{{ $item->description }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">{{ $item->cheque_number ?? '—' }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; text-align: right; color: #dc2626; font-weight: 600;">KES {{ number_format((float) $item->money_out, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            @endif

            {{-- Unmatched Cheque Transactions --}}
            @if ($summary['unmatched_cheques']->isNotEmpty())
                <x-filament::section heading="Unmatched Cheque Transactions" description="These cheque transactions from the bank statement could not be matched to any cheque in the system." icon="heroicon-o-question-mark-circle" collapsible>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                            <thead>
                                <tr style="background: #fef3c7;">
                                    <th style="text-align: left; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Date</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Type</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Cheque #</th>
                                    <th style="text-align: left; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Description</th>
                                    <th style="text-align: right; border-bottom: 2px solid #fde68a; padding: 0.6rem;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary['unmatched_cheques'] as $item)
                                    <tr>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">{{ $item->transaction_date?->format('d M Y') }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">
                                            <x-filament::badge :color="$item->getTypeColor()" size="sm">{{ $item->getTypeLabel() }}</x-filament::badge>
                                        </td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; font-weight: 600;">{{ $item->cheque_number ?? '—' }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; max-width: 300px; word-wrap: break-word;">{{ $item->description }}</td>
                                        <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; text-align: right;">KES {{ number_format($item->money_out > 0 ? (float) $item->money_out : (float) $item->money_in, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            @endif

            {{-- All Transactions --}}
            <x-filament::section heading="All Transactions" icon="heroicon-o-list-bullet" collapsible collapsed>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="background: #f9fafb;">
                                <th style="text-align: left; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Date</th>
                                <th style="text-align: left; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Description</th>
                                <th style="text-align: left; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Type</th>
                                <th style="text-align: right; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Money Out</th>
                                <th style="text-align: right; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Money In</th>
                                <th style="text-align: right; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Ledger Balance</th>
                                <th style="text-align: left; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Cheque #</th>
                                <th style="text-align: left; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Match</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($summary['items'] as $item)
                                <tr>
                                    <td style="padding: 0.45rem; border-bottom: 1px solid #e5e7eb;">{{ $item->transaction_date?->format('d M Y') ?? '—' }}</td>
                                    <td style="padding: 0.45rem; border-bottom: 1px solid #e5e7eb; max-width: 250px; word-wrap: break-word;">{{ $item->description }}</td>
                                    <td style="padding: 0.45rem; border-bottom: 1px solid #e5e7eb;">
                                        <x-filament::badge :color="$item->getTypeColor()" size="sm">{{ $item->getTypeLabel() }}</x-filament::badge>
                                    </td>
                                    <td style="padding: 0.45rem; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $item->money_out > 0 ? number_format((float) $item->money_out, 2) : '—' }}</td>
                                    <td style="padding: 0.45rem; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ $item->money_in > 0 ? number_format((float) $item->money_in, 2) : '—' }}</td>
                                    <td style="padding: 0.45rem; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ number_format((float) $item->ledger_balance, 2) }}</td>
                                    <td style="padding: 0.45rem; border-bottom: 1px solid #e5e7eb;">{{ $item->cheque_number ?? '—' }}</td>
                                    <td style="padding: 0.45rem; border-bottom: 1px solid #e5e7eb;">
                                        @if ($item->is_matched)
                                            <x-filament::badge color="success" size="sm">✓</x-filament::badge>
                                        @elseif ($item->cheque_number)
                                            <x-filament::badge color="danger" size="sm">✗</x-filament::badge>
                                        @else
                                            <span style="color: #9ca3af;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- Action Buttons --}}
            @if ($reconciliation->isDraft())
                <div style="display: flex; gap: 1rem; justify-content: flex-end; flex-wrap: wrap;">
                    <x-filament::button color="danger" wire:click="discardReconciliation" wire:confirm="Are you sure you want to discard this reconciliation? This cannot be undone." icon="heroicon-o-trash" outlined>
                        Discard
                    </x-filament::button>
                    <x-filament::button color="success" wire:click="applyReconciliation" wire:confirm="Apply this reconciliation? This will update cheque statuses (mark cleared cheques as cleared, mark bounced cheques as returned). This cannot be undone." icon="heroicon-o-check">
                        Apply Reconciliation
                    </x-filament::button>
                </div>
            @endif
        @endif

        {{-- Reconciliation History --}}
        @php($history = $this->getReconciliationHistory())
        @if ($history->isNotEmpty())
            <x-filament::section heading="Reconciliation History" icon="heroicon-o-clock" collapsible collapsed>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                        <thead>
                            <tr>
                                <th style="text-align: left; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Date</th>
                                <th style="text-align: left; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Period</th>
                                <th style="text-align: left; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Account</th>
                                <th style="text-align: center; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Cleared</th>
                                <th style="text-align: center; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Bounced</th>
                                <th style="text-align: right; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Penalties</th>
                                <th style="text-align: left; border-bottom: 2px solid #d1d5db; padding: 0.55rem;">Status</th>
                                <th style="border-bottom: 2px solid #d1d5db; padding: 0.55rem;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $rec)
                                <tr @if($rec->id === $this->reconciliationId) style="background: #eff6ff;" @endif>
                                    <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">{{ $rec->created_at->format('d M Y H:i') }}</td>
                                    <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">
                                        {{ $rec->statement_period_start?->format('d M') }} — {{ $rec->statement_period_end?->format('d M Y') }}
                                    </td>
                                    <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">{{ $rec->account_number }}</td>
                                    <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; text-align: center;">{{ $rec->total_cheques_cleared }}</td>
                                    <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; text-align: center;">{{ $rec->total_cheques_bounced }}</td>
                                    <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb; text-align: right;">KES {{ number_format((float) $rec->total_penalties, 2) }}</td>
                                    <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">
                                        @if ($rec->isApplied())
                                            <x-filament::badge color="success" size="sm">Applied</x-filament::badge>
                                        @else
                                            <x-filament::badge color="warning" size="sm">Draft</x-filament::badge>
                                        @endif
                                    </td>
                                    <td style="padding: 0.55rem; border-bottom: 1px solid #e5e7eb;">
                                        <x-filament::button size="xs" color="gray" wire:click="loadReconciliation({{ $rec->id }})" outlined>
                                            View
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
