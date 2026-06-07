<?php

namespace App\Filament\Widgets;

use App\Enums\Currency;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlySalesChartWidget extends ChartWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'გაყიდვები თვის მიხედვით';

    protected ?string $maxHeight = '320px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('YEAR(orders.sold_at) as yr, MONTH(orders.sold_at) as mo, products.currency_id, SUM(order_items.total_price) as total')
            ->whereNotNull('orders.sold_at')
            ->groupBy('yr', 'mo', 'products.currency_id')
            ->orderBy('yr')
            ->orderBy('mo')
            ->get();

        // Collect all year-month labels in order
        $labels = $rows
            ->map(fn ($r) => $r->yr.'-'.str_pad($r->mo, 2, '0', STR_PAD_LEFT))
            ->unique()
            ->values();

        $monthNames = [
            1 => 'იანვარი', 2 => 'თებერვალი', 3 => 'მარტი',
            4 => 'აპრილი', 5 => 'მაისი', 6 => 'ივნისი',
            7 => 'ივლისი', 8 => 'აგვისტო', 9 => 'სექტემბერი',
            10 => 'ოქტომბერი', 11 => 'ნოემბერი', 12 => 'დეკემბერი',
        ];

        $displayLabels = $labels->map(function ($key) use ($monthNames) {
            [$yr, $mo] = explode('-', $key);

            return $monthNames[(int) $mo].' '.$yr;
        });

        // Bucket totals by label+currency
        $gelTotals = [];
        $usdTotals = [];

        foreach ($labels as $key) {
            $gelTotals[$key] = 0.0;
            $usdTotals[$key] = 0.0;
        }

        foreach ($rows as $row) {
            $key = $row->yr.'-'.str_pad($row->mo, 2, '0', STR_PAD_LEFT);
            $currency = Currency::fromId((int) $row->currency_id);

            if ($currency === Currency::GEL) {
                $gelTotals[$key] += (float) $row->total;
            } elseif ($currency === Currency::USD) {
                $usdTotals[$key] += (float) $row->total;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => '₾ GEL',
                    'data' => array_values($gelTotals),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => '$ USD',
                    'data' => array_values($usdTotals),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $displayLabels->values()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
