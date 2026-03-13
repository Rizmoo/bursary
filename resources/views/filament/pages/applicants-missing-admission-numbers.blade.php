<x-filament-panels::page>
    @php($rows = $this->getRows())

    <div style="display: grid; gap: 1rem;">
        <x-filament::section heading="Applicants Missing Admission Numbers" description="Applicants listed here cannot be awarded bursary until an admission number is provided.">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
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

                <x-filament::section heading="Total Missing Admission" compact>
                    <div style="font-size: 1.5rem; font-weight: 700;">{{ number_format($rows->count()) }}</div>
                </x-filament::section>

                <x-filament::section heading="Awarded (Data Issue)" compact>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #dc2626;">{{ number_format($this->getAwardedWithoutAdmissionCount()) }}</div>
                </x-filament::section>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                    <thead>
                        <tr>
                            <th style="text-align: left; border-bottom: 1px solid #d1d5db; padding: 0.65rem;">Name</th>
                            <th style="text-align: left; border-bottom: 1px solid #d1d5db; padding: 0.65rem;">Institution</th>
                            <th style="text-align: left; border-bottom: 1px solid #d1d5db; padding: 0.65rem;">Category</th>
                            <th style="text-align: left; border-bottom: 1px solid #d1d5db; padding: 0.65rem;">Need Score</th>
                            <th style="text-align: left; border-bottom: 1px solid #d1d5db; padding: 0.65rem;">Awarded</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $applicant)
                            <tr>
                                <td style="padding: 0.65rem; border-bottom: 1px solid #e5e7eb;">{{ trim(collect([$applicant->first_name, $applicant->other_name, $applicant->last_name])->filter()->implode(' ')) }}</td>
                                <td style="padding: 0.65rem; border-bottom: 1px solid #e5e7eb;">{{ $applicant->institution?->name }}</td>
                                <td style="padding: 0.65rem; border-bottom: 1px solid #e5e7eb;">{{ $applicant->institution?->category?->name }}</td>
                                <td style="padding: 0.65rem; border-bottom: 1px solid #e5e7eb;">{{ $applicant->need_assessment }}</td>
                                <td style="padding: 0.65rem; border-bottom: 1px solid #e5e7eb;">KES {{ number_format((float) $applicant->amount_awarded, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 0.85rem; text-align: center; color: #6b7280;">No applicants missing admission numbers for this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
