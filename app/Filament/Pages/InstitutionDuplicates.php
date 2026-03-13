<?php

namespace App\Filament\Pages;

use App\Models\FinancialYear;
use App\Models\Institution;
use App\Services\InstitutionDuplicateMatcherService;
use App\Services\InstitutionMergeService;
use App\Support\FinancialYearScope;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

class InstitutionDuplicates extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string | UnitEnum | null $navigationGroup = 'Institutions';

    protected static ?string $navigationLabel = 'Duplicate Institutions';

    protected static ?string $title = 'Duplicate Institution Detection';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.institution-duplicates';

    public ?int $financialYearId = null;

    public ?int $manualSourceId = null;

    public ?int $manualTargetId = null;

    public function mount(): void
    {
        $this->financialYearId = optional(FinancialYearScope::resolveForTenant(filament()->getTenant()?->getKey()))->getKey();
    }

    /**
     * @return Collection<int, string>
     */
    public function getFinancialYearOptions(): Collection
    {
        return FinancialYear::query()
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->orderByDesc('start_date')
            ->pluck('name', 'id');
    }

    /**
     * @return Collection<int, string>
     */
    public function getInstitutionOptions(): Collection
    {
        return Institution::query()
            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    /**
     * @return Collection<int, array<string,mixed>>
     */
    public function getPotentialDuplicates(): Collection
    {
        $tenant = filament()->getTenant();

        if (! $tenant) {
            return collect();
        }

        return app(InstitutionDuplicateMatcherService::class)
            ->findPotentialDuplicates($tenant, $this->financialYearId);
    }

    public function mergePair(int $sourceId, int $targetId): void
    {
        $tenant = filament()->getTenant();

        if (! $tenant) {
            return;
        }

        $result = app(InstitutionMergeService::class)->merge($tenant, $sourceId, $targetId);

        Notification::make()
            ->title('Institutions merged successfully')
            ->body('Applicants moved: ' . number_format($result['applicants_updated']) . '. Cheques moved: ' . number_format($result['cheques_updated']) . '.')
            ->success()
            ->send();
    }

    public function mergeManual(): void
    {
        if (blank($this->manualSourceId) || blank($this->manualTargetId)) {
            Notification::make()
                ->title('Select both source and target institution')
                ->warning()
                ->send();

            return;
        }

        $this->mergePair((int) $this->manualSourceId, (int) $this->manualTargetId);

        $this->manualSourceId = null;
        $this->manualTargetId = null;
    }
}
