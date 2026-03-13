@php
    $tenant = filament()->getTenant();
    $tenantId = $tenant?->getKey();

    $options = collect();
    $selectedId = null;

    if ($tenantId) {
        $options = \App\Models\FinancialYear::query()
            ->where('ward_id', $tenantId)
            ->orderByDesc('start_date')
            ->pluck('name', 'id');

        $selectedId = optional(\App\Support\FinancialYearScope::resolveForTenant($tenantId))->getKey();
    }
@endphp

@if ($tenantId && $options->isNotEmpty())
    <form method="POST" action="{{ route('financial-year-scope.set', ['tenant' => $tenant]) }}" style="display:flex; align-items:center; gap:.5rem; margin-inline-end:.75rem;">
        @csrf
        <label for="financial_year_scope" style="font-size:.78rem; color:#6b7280; white-space:nowrap;">Financial Year</label>
        <select id="financial_year_scope" name="financial_year_id" onchange="this.form.submit()" style="font-size:.82rem; border:1px solid #d1d5db; border-radius:.4rem; padding:.25rem .45rem; background:white; min-width:180px;">
            @foreach ($options as $id => $name)
                <option value="{{ $id }}" @selected((int) $selectedId === (int) $id)>{{ $name }}</option>
            @endforeach
        </select>
    </form>
@endif
