<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('order_number')
                ->label('შეკვეთის ნომერი'),
            ExportColumn::make('total')
                ->label('ფასი'),
            ExportColumn::make('currency')
                ->label('ვალუტა')
                ->state(fn (): string => 'GEL'),
            ExportColumn::make('sold_at')
                ->label('გაყიდვის თარიღი'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = number_format($export->successful_rows);

        return "შეკვეთების ექსპორტი დასრულდა. {$count} ჩანაწერი წარმატებით ექსპორტირდა.";
    }
}
