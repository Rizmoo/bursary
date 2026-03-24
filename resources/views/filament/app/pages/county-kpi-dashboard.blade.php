<x-filament-panels::page>
    @php($stats = $this->getStats())

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <x-filament::section>
            <x-slot name="heading">Wards</x-slot>
            <div class="text-3xl font-semibold">{{ number_format($stats['wards']) }}</div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Ward Users</x-slot>
            <div class="text-3xl font-semibold">{{ number_format($stats['ward_users']) }}</div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Applicants</x-slot>
            <div class="text-3xl font-semibold">{{ number_format($stats['applicants']) }}</div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Beneficiaries</x-slot>
            <div class="text-3xl font-semibold">{{ number_format($stats['beneficiaries']) }}</div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Institutions</x-slot>
            <div class="text-3xl font-semibold">{{ number_format($stats['institutions']) }}</div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Total Awarded (KES)</x-slot>
            <div class="text-3xl font-semibold">{{ number_format($stats['total_awarded'], 2) }}</div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
