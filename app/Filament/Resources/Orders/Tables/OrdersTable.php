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
                    ->icon('heroicon-o-hashtag')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('კლიენტი')
                    ->icon('heroicon-o-user')
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
                    ->icon(function ($state): string {
                        $value = $state instanceof OrderStatus ? $state->value : (string) $state;

                        return match ($value) {
                            OrderStatus::Draft->value => 'heroicon-o-document-text',
                            OrderStatus::Confirmed->value => 'heroicon-o-check-circle',
                            OrderStatus::Completed->value => 'heroicon-o-check-badge',
                            OrderStatus::Cancelled->value => 'heroicon-o-x-circle',
                            default => 'heroicon-o-question-mark-circle',
                        };
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
                    ->icon(function ($state): string {
                        if ($state === null) {
                            return 'heroicon-o-minus-circle';
                        }

                        $value = $state instanceof PaymentStatus ? $state->value : (string) $state;

                        return match ($value) {
                            PaymentStatus::Paid->value => 'heroicon-o-credit-card',
                            PaymentStatus::Unpaid->value => 'heroicon-o-exclamation-circle',
                            PaymentStatus::Partial->value => 'heroicon-o-adjustments-horizontal',
                            PaymentStatus::Refunded->value => 'heroicon-o-arrow-uturn-left',
                            default => 'heroicon-o-question-mark-circle',
                        };
                    })
                    ->label('გადახდა')
                    ->sortable()
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
                    ->icon(function ($state): string {
                        if ($state === null) {
                            return 'heroicon-o-minus-circle';
                        }

                        $value = $state instanceof SaleChannel ? $state->value : (string) $state;

                        return match ($value) {
                            SaleChannel::Internal->value => 'heroicon-o-building-storefront',
                            SaleChannel::MyParts->value => 'heroicon-o-globe-alt',
                            SaleChannel::Other->value => 'heroicon-o-ellipsis-horizontal-circle',
                            default => 'heroicon-o-question-mark-circle',
                        };
                    })
                    ->color(function ($state): string {
                        if ($state === null) {
                            return 'gray';
                        }

                        $value = $state instanceof SaleChannel ? $state->value : (string) $state;

                        return match ($value) {
                            SaleChannel::Internal->value => 'success',
                            SaleChannel::MyParts->value => 'info',
                            SaleChannel::Other->value => 'warning',
                            default => 'gray',
                        };
                    })
                    ->label('არხი')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total')
                    ->label('სულ')
                    ->money('GEL')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->weight('semibold')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('sold_at')
                    ->label('გაყიდვის თარიღი')
                    ->dateTime()
                    ->icon('heroicon-o-calendar-days')
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
                SelectFilter::make('customer_id')
                    ->label('კლიენტი')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->emptyStateHeading('მონაცემები ვერ მოიძებნა')
            ->defaultSort('sold_at', 'desc')
            ->recordActions([
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
