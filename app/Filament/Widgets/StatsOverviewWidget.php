<?php

namespace App\Filament\Widgets;

use App\Enums\Currency;
use App\Models\MarketplaceListing;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'მთავარი მაჩვენებლები';

    protected function getStats(): array
    {
        $totalProducts = Product::query()->where('is_active', true)->count();
        $activeListings = MarketplaceListing::query()->count();
        $stockData = Product::query()
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM(quantity_in_stock), 0) as total_qty, COALESCE(SUM(quantity_in_stock * cost_price), 0) as total_value')
            ->first();
        $totalQty = (int) ($stockData->total_qty ?? 0);
        $totalValue = (float) ($stockData->total_value ?? 0);

        $valuesByCurrency = Product::query()
            ->where('is_active', true)
            ->whereNotNull('sale_price')
            ->selectRaw('currency_id, COALESCE(SUM(quantity_in_stock * sale_price), 0) as total_value')
            ->groupBy('currency_id')
            ->pluck('total_value', 'currency_id');

        $currencyStats = [];
        foreach ([Currency::GEL, Currency::USD] as $currency) {
            $value = 0.0;
            foreach ($valuesByCurrency as $currencyId => $total) {
                if (Currency::fromId((int) $currencyId) === $currency) {
                    $value += (float) $total;
                }
            }

            $currencyStats[] = Stat::make(
                'ჯამური ღირებულება ('.$currency->value.')',
                number_format($value, 2)
            )
                ->description('პროდუქტების ჯამი '.$currency->value.'-ში')
                ->icon('heroicon-o-banknotes')
                ->color('success');
        }

        return [
            Stat::make('პროდუქტები', number_format($totalProducts))
                ->description('აქტიური პროდუქტების რაოდენობა')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary'),
            // Stat::make('განცხადებები', number_format($activeListings))
            //     ->description('საბაზრო განცხადებების რაოდენობა')
            //     ->icon('heroicon-o-globe-alt')
            //     ->color('info'),
            Stat::make('საწყობში', number_format($totalQty))
                ->description('პროდუქტების ჯამური რაოდენობა')
                ->icon('heroicon-o-cube')
                ->color('success'),
            // Stat::make('საწყობის ღირებულება', MoneyFormatter::format($totalValue, Currency::GEL))
            //     ->description('ღირებულების მიხედვით')
            //     ->icon('heroicon-o-currency-dollar')
            //     ->color('warning'),
            ...$currencyStats,
        ];
    }
}
