<x-filament-panels::page>
    @php($rows = $this->getPotentialDuplicates())

    <div style="display:grid; gap:1rem;">
        <x-filament::section heading="Duplicate Detection" description="Potential duplicate institutions are detected using normalized name matching, token similarity, edit distance, and code variants.">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:1rem;">
                <div>
                    <label class="fi-fo-field-wrp-label">Financial Year (for applicant counts)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="financialYearId">
                            @foreach ($this->getFinancialYearOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <x-filament::section heading="Potential Pairs" compact>
                    <div style="font-size:1.5rem; font-weight:700;">{{ number_format($rows->count()) }}</div>
                </x-filament::section>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:.9rem;">
                    <thead>
                        <tr>
                            <th style="text-align:left; border-bottom:1px solid #d1d5db; padding:.6rem;">Source (to merge from)</th>
                            <th style="text-align:left; border-bottom:1px solid #d1d5db; padding:.6rem;">Target (to keep)</th>
                            <th style="text-align:right; border-bottom:1px solid #d1d5db; padding:.6rem;">Score</th>
                            <th style="text-align:left; border-bottom:1px solid #d1d5db; padding:.6rem;">Reason</th>
                            <th style="text-align:right; border-bottom:1px solid #d1d5db; padding:.6rem;">Applicants (FY)</th>
                            <th style="text-align:left; border-bottom:1px solid #d1d5db; padding:.6rem;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td style="padding:.55rem; border-bottom:1px solid #e5e7eb;">
                                    <div style="font-weight:600;">{{ $row['source_name'] }}</div>
                                    <div style="font-size:.78rem; color:#6b7280;">ID: {{ $row['source_id'] }}</div>
                                </td>
                                <td style="padding:.55rem; border-bottom:1px solid #e5e7eb;">
                                    <div style="font-weight:600;">{{ $row['target_name'] }}</div>
                                    <div style="font-size:.78rem; color:#6b7280;">ID: {{ $row['target_id'] }}</div>
                                </td>
                                <td style="padding:.55rem; border-bottom:1px solid #e5e7eb; text-align:right; font-weight:600;">{{ $row['score'] }}%</td>
                                <td style="padding:.55rem; border-bottom:1px solid #e5e7eb;">{{ $row['reason'] }}</td>
                                <td style="padding:.55rem; border-bottom:1px solid #e5e7eb; text-align:right;">
                                    {{ number_format((int) $row['source_count']) }} → {{ number_format((int) $row['target_count']) }}
                                </td>
                                <td style="padding:.55rem; border-bottom:1px solid #e5e7eb;">
                                    <x-filament::button
                                        size="sm"
                                        color="warning"
                                        wire:click="mergePair({{ $row['source_id'] }}, {{ $row['target_id'] }})"
                                        wire:confirm="Merge these institutions? This will move applicants and cheques, then delete the source institution."
                                    >
                                        Merge
                                    </x-filament::button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding:.8rem; text-align:center; color:#6b7280;">No strong duplicate candidates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section heading="Manual Merge" description="Use this when you know two institutions should be merged but they are not listed above.">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:1rem; align-items:end;">
                <div>
                    <label class="fi-fo-field-wrp-label">Source Institution (will be removed)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="manualSourceId">
                            <option value="">-- Select --</option>
                            @foreach ($this->getInstitutionOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="fi-fo-field-wrp-label">Target Institution (will remain)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="manualTargetId">
                            <option value="">-- Select --</option>
                            @foreach ($this->getInstitutionOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <x-filament::button
                        color="warning"
                        wire:click="mergeManual"
                        wire:confirm="Merge manually selected institutions? This action cannot be undone."
                    >
                        Merge Institutions
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
