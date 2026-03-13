<?php

namespace App\Filament\Resources\Applicants\Actions;

use App\Models\Applicant;
use App\Models\FinancialYear;
use App\Services\AwardHelperCalculatorService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class AwardHelperCalculatorAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'awardHelperCalculator';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Award Helper Calculator')
            ->icon('heroicon-o-calculator')
            ->color('warning')
            ->modalHeading('Bursary Award Helper Calculator')
            ->modalDescription('Set available amount and weight matrix. Universities/Tertiary get higher share, Boarding middle, Day lower. Need assessment is used in allocation.')
            ->modalWidth('5xl')
            ->form([
                Select::make('financial_year_id')
                    ->label('Financial Year')
                    ->options(
                        fn () => FinancialYear::query()
                            ->when(filament()->getTenant(), fn ($query, $tenant) => $query->where('ward_id', $tenant->getKey()))
                            ->orderByDesc('start_date')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if (blank($state)) {
                            return;
                        }

                        $tenant = filament()->getTenant();

                        $financialYear = FinancialYear::query()
                            ->when($tenant, fn ($query) => $query->where('ward_id', $tenant->getKey()))
                            ->find((int) $state);

                        if (! $financialYear) {
                            return;
                        }

                        $awarded = (float) Applicant::query()
                            ->where('ward_id', $financialYear->ward_id)
                            ->where('financial_year_id', $financialYear->id)
                            ->sum('amount_awarded');

                        $remainingBudget = round(
                            ((float) $financialYear->budget)
                            - $awarded
                            + ((float) ($financialYear->unutilised_amount ?? 0)),
                            2,
                        );

                        $set('available_amount', max($remainingBudget, 0));
                    })
                    ->live(),

                TextInput::make('available_amount')
                    ->label('Available Amount (KES)')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->live(),

                TextInput::make('min_need_assessment')
                    ->label('Minimum Need Assessment')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->live(),

                Toggle::make('orphans_only')
                    ->label('Only Orphans')
                    ->default(false)
                    ->live(),

                Toggle::make('exclude_cheque_assigned')
                    ->label('Exclude Applicants Already in Cheques')
                    ->default(true)
                    ->live(),

                Toggle::make('overwrite_existing_awards')
                    ->label('Overwrite Existing Awards')
                    ->default(false)
                    ->live(),

                TextInput::make('weight_university')
                    ->label('Weight - University')
                    ->numeric()
                    ->default(1.6)
                    ->required()
                    ->live(),
                TextInput::make('weight_tertiary')
                    ->label('Weight - Tertiary')
                    ->numeric()
                    ->default(1.35)
                    ->required()
                    ->live(),
                TextInput::make('weight_boarding')
                    ->label('Weight - Boarding')
                    ->numeric()
                    ->default(1.1)
                    ->required()
                    ->live(),
                TextInput::make('weight_day')
                    ->label('Weight - Day')
                    ->numeric()
                    ->default(0.8)
                    ->required()
                    ->live(),
                TextInput::make('weight_other')
                    ->label('Weight - Other')
                    ->numeric()
                    ->default(1.0)
                    ->required()
                    ->live(),

                Placeholder::make('matrix_preview')
                    ->label('Preview Matrix')
                    ->content(function (callable $get): HtmlString {
                        $tenant = filament()->getTenant();
                        if (! $tenant || blank($get('financial_year_id')) || blank($get('available_amount'))) {
                            return new HtmlString('Set financial year and available amount to preview allocation.');
                        }

                        $preview = app(AwardHelperCalculatorService::class)->preview($tenant, [
                            'financial_year_id' => $get('financial_year_id'),
                            'available_amount' => $get('available_amount'),
                            'min_need_assessment' => $get('min_need_assessment') ?? 0,
                            'orphans_only' => (bool) $get('orphans_only'),
                            'exclude_cheque_assigned' => (bool) $get('exclude_cheque_assigned'),
                            'overwrite_existing_awards' => (bool) $get('overwrite_existing_awards'),
                            'weight_university' => $get('weight_university'),
                            'weight_tertiary' => $get('weight_tertiary'),
                            'weight_boarding' => $get('weight_boarding'),
                            'weight_day' => $get('weight_day'),
                            'weight_other' => $get('weight_other'),
                        ]);

                        $m = $preview['matrix'];

                        $html = '<div style="display:grid; gap:.5rem;">';
                        $html .= '<div><strong>Eligible applicants:</strong> '.number_format((int) $preview['eligible_count']).'</div>';
                        $html .= '<div><strong>Total to award:</strong> KES '.number_format((float) $preview['awarded_total'], 2).' | <strong>Remaining:</strong> KES '.number_format((float) $preview['remaining'], 2).'</div>';
                        $html .= '<table style="width:100%; border-collapse:collapse; font-size:.88rem;">';
                        $html .= '<thead><tr><th style="text-align:left; border:1px solid #d1d5db; padding:.35rem;">Category</th><th style="text-align:right; border:1px solid #d1d5db; padding:.35rem;">Applicants</th><th style="text-align:right; border:1px solid #d1d5db; padding:.35rem;">Amount</th><th style="text-align:right; border:1px solid #d1d5db; padding:.35rem;">Average</th></tr></thead><tbody>';

                        foreach (['university', 'tertiary', 'boarding', 'day', 'other'] as $key) {
                            $row = $m[$key];
                            $html .= '<tr>';
                            $html .= '<td style="border:1px solid #e5e7eb; padding:.35rem; text-transform:capitalize;">'.$key.'</td>';
                            $html .= '<td style="border:1px solid #e5e7eb; padding:.35rem; text-align:right;">'.number_format((int) $row['count']).'</td>';
                            $html .= '<td style="border:1px solid #e5e7eb; padding:.35rem; text-align:right;">'.number_format((float) $row['amount'], 2).'</td>';
                            $html .= '<td style="border:1px solid #e5e7eb; padding:.35rem; text-align:right;">'.number_format((float) $row['avg'], 2).'</td>';
                            $html .= '</tr>';
                        }

                        $html .= '</tbody></table></div>';

                        return new HtmlString($html);
                    }),
            ])
            ->action(function (array $data): void {
                $tenant = filament()->getTenant();

                $result = app(AwardHelperCalculatorService::class)->apply($tenant, $data);

                Notification::make()
                    ->title('Awards applied successfully')
                    ->body('Awarded '.number_format((int) $result['eligible_count']).' applicant(s). Total: KES '.number_format((float) $result['awarded_total'], 2).'. Remaining: KES '.number_format((float) $result['remaining'], 2))
                    ->success()
                    ->send();
            });
    }
}
