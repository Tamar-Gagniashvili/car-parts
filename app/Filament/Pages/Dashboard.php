<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MonthlySalesChartWidget;
use App\Filament\Widgets\MonthlyOrdersCountChartWidget;
use App\Filament\Widgets\MonthlyProductsSoldChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'დაშბორდი';

    protected static ?string $title = 'დაშბორდი';

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            MonthlySalesChartWidget::class,
            MonthlyProductsSoldChartWidget::class,
            MonthlyOrdersCountChartWidget::class,
        ];
    }
}
