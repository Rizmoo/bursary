<x-filament-panels::page>
    @php($report = $this->getReportData())

    <div style="display: grid; gap: 1rem;">
        <x-filament::section heading="Filters" description="Choose financial year and quarter for this report.">
            <x-slot name="afterHeader">
                @if ($this->getExportUrl())
                    <x-filament::button tag="a" :href="$this->getExportUrl()" icon="heroicon-o-arrow-down-tray" color="primary">
                        Export Excel
                    </x-filament::button>
                @endif
            </x-slot>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div>
                    <label class="fi-fo-field-wrp-label">Financial Year</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="financialYearId">
                            @foreach ($this->getFinancialYearOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="fi-fo-field-wrp-label">Quarter</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="quarter">
                            <option value="1">Q1</option>
                            <option value="2">Q2</option>
                            <option value="3">Q3</option>
                            <option value="4">Q4</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-filament::section>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <x-filament::section heading="Total Beneficiaries">
                <div style="font-size: 1.6rem; font-weight: 700;">{{ number_format($report['totals']['beneficiaries']) }}</div>
            </x-filament::section>

            <x-filament::section heading="Total Awarded">
                <div style="font-size: 1.6rem; font-weight: 700;">KES {{ number_format($report['totals']['total_awarded'], 2) }}</div>
            </x-filament::section>
        </div>

        <x-filament::section heading="Quarter Financial Inputs" description="These values are used in the exported statement.">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div>
                    <label class="fi-fo-field-wrp-label">Opening Bank Balance</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" step="0.01" wire:model.live="openingBalance" />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="fi-fo-field-wrp-label">Money Deposited</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" step="0.01" wire:model.live="moneyDeposited" />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="fi-fo-field-wrp-label">Administration Cost</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" step="0.01" wire:model.live="administrationCost" />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="fi-fo-field-wrp-label">Bank Charges</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" step="0.01" wire:model.live="bankCharges" />
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section heading="Institution Level Summary">
            <p style="margin-bottom: 0.75rem; color: var(--gray-600);">
                @if ($report['period_start'] && $report['period_end'])
                    {{ $report['period_start']->format('d M Y') }} - {{ $report['period_end']->format('d M Y') }}
                @else
                    Select a financial year to view the report.
                @endif
            </p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                    <thead>
                        <tr>
                            <th style="text-align: left; border-bottom: 1px solid #d1d5db; padding: 0.65rem;">Institution Level</th>
                            <th style="text-align: left; border-bottom: 1px solid #d1d5db; padding: 0.65rem;">Beneficiaries</th>
                            <th style="text-align: left; border-bottom: 1px solid #d1d5db; padding: 0.65rem;">Total Awarded</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['rows'] as $row)
                            <tr>
                                <td style="padding: 0.65rem; border-bottom: 1px solid #e5e7eb;">{{ $row['name'] }}</td>
                                <td style="padding: 0.65rem; border-bottom: 1px solid #e5e7eb;">{{ number_format($row['beneficiaries']) }}</td>
                                <td style="padding: 0.65rem; border-bottom: 1px solid #e5e7eb;">KES {{ number_format($row['total_awarded'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="padding: 0.85rem; text-align: center; color: #6b7280;">No quarterly data available for the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="padding: 0.65rem; font-weight: 700; border-top: 1px solid #d1d5db;">Total</td>
                            <td style="padding: 0.65rem; font-weight: 700; border-top: 1px solid #d1d5db;">{{ number_format($report['totals']['beneficiaries']) }}</td>
                            <td style="padding: 0.65rem; font-weight: 700; border-top: 1px solid #d1d5db;">KES {{ number_format($report['totals']['total_awarded'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
