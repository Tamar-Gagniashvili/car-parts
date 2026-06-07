<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyProductsSoldChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'გაყიდული პროდუქტები თვის მიხედვით';

    protected ?string $description = 'გაყიდული ერთეულების რაოდენობა';

    protected ?string $maxHeight = '280px';

    protected string $color = 'violet';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $rows = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('YEAR(orders.sold_at) as yr, MONTH(orders.sold_at) as mo, SUM(order_items.quantity) as total_qty')
            ->whereNotNull('orders.sold_at')
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
        $data = $rows->map(fn ($r) => (int) $r->total_qty)->all();

        return [
            'datasets' => [
                [
                    'label' => 'გაყიდული ერთეულები',
                    'data' => $data,
                    'borderColor' => 'rgb(139, 92, 246)',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.15)',
                    'pointBackgroundColor' => 'rgb(139, 92, 246)',
                    'pointRadius' => 5,
                    'pointHoverRadius' => 8,
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 2,
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
