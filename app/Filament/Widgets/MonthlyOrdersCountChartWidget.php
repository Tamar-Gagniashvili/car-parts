<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyOrdersCountChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'შეკვეთები თვის მიხედვით';

    protected ?string $description = 'შეკვეთების რაოდენობა';

    protected ?string $maxHeight = '280px';

    protected string $color = 'amber';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = DB::table('orders')
            ->selectRaw('YEAR(sold_at) as yr, MONTH(sold_at) as mo, COUNT(*) as total_orders')
            ->whereNotNull('sold_at')
            ->groupBy('yr', 'mo')
            ->orderBy('yr')
            ->orderBy('mo')
            ->get();

        $monthNames = [
            1 => 'იანვ', 2 => 'თებ', 3 => 'მარ', 4 => 'აპრ',
            5 => 'მაი', 6 => 'ივნ', 7 => 'ივლ', 8 => 'აგვ',
            9 => 'სექ', 10 => 'ოქტ', 11 => 'ნოე', 12 => 'დეკ',
        ];

        $labels = $rows->map(fn ($r) => $monthNames[$r->mo].' '.$r->yr)->all();
        $data = $rows->map(fn ($r) => (int) $r->total_orders)->all();

        $backgroundColors = array_map(
            fn ($i) => 'rgba(251, 146, 60, '.round(0.5 + ($i / max(count($data) - 1, 1)) * 0.45, 2).')',
            array_keys($data)
        );

        return [
            'datasets' => [
                [
                    'label' => 'შეკვეთები',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => 'rgb(234, 88, 12)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['stepSize' => 1],
                ],
            ],
        ];
    }
}
