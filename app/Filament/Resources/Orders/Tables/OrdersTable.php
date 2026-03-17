<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SaleChannel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('შეკვეთის ნომერი')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('კლიენტი')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $value = $state instanceof OrderStatus ? $state->value : (string) $state;

                        return OrderStatus::from($value)->label();
                    })
                    ->color(function ($state): string {
                        $value = $state instanceof OrderStatus ? $state->value : (string) $state;

                        return OrderStatus::colors()[$value] ?? 'gray';
                    })
                    ->label('სტატუსი')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->formatStateUsing(function ($state): ?string {
                        if ($state === null) {
                            return null;
                        }

                        $value = $state instanceof PaymentStatus ? $state->value : (string) $state;

                        return PaymentStatus::from($value)->label();
                    })
                    ->color(function ($state): string {
                        if ($state === null) {
                            return 'gray';
                        }

                        $value = $state instanceof PaymentStatus ? $state->value : (string) $state;

                        return PaymentStatus::colors()[$value] ?? 'gray';
                    })
                    ->label('გადახდა')
                    ->toggleable(),
                TextColumn::make('sale_channel')
                    ->badge()
                    ->formatStateUsing(function ($state): ?string {
                        if ($state === null) {
                            return null;
                        }

                        $value = $state instanceof SaleChannel ? $state->value : (string) $state;

                        return SaleChannel::from($value)->label();
                    })
                    ->label('არხი')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total')
                    ->label('სულ')
                    ->money('GEL')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('sold_at')
                    ->label('გაყიდვის თარიღი')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('სტატუსი')
                    ->options(OrderStatus::options()),
                SelectFilter::make('payment_status')
                    ->label('გადახდა')
                    ->options(PaymentStatus::options()),
                SelectFilter::make('sale_channel')
                    ->label('არხი')
                    ->options(SaleChannel::options()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
