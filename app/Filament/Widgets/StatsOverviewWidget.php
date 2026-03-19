<?php

namespace App\Filament\Widgets;

use App\Enums\Currency;
use App\Models\MarketplaceListing;
use App\Models\Product;
use App\Support\MoneyFormatter;
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

        return [
            Stat::make('პროდუქტები', number_format($totalProducts))
                ->description('აქტიური პროდუქტების რაოდენობა')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary'),
            Stat::make('განცხადებები', number_format($activeListings))
                ->description('საბაზრო განცხადებების რაოდენობა')
                ->icon('heroicon-o-globe-alt')
                ->color('info'),
            Stat::make('საწყობში', number_format($totalQty))
                ->description('პროდუქტების ჯამური რაოდენობა')
                ->icon('heroicon-o-cube')
                ->color('success'),
            Stat::make('საწყობის ღირებულება', MoneyFormatter::format($totalValue, Currency::GEL))
                ->description('ღირებულების მიხედვით')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning'),
        ];
    }
}
