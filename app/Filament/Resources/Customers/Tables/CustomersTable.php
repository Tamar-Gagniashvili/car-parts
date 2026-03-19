<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('სახელი')
                    ->icon('heroicon-o-user')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('ტელეფონი')
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('ელ-ფოსტა')
                    ->icon('heroicon-o-envelope')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('source')
                    ->label('წყარო')
                    ->badge()
                    ->icon('heroicon-o-signal')
                    ->color(function ($state): string {
                        $value = (string) $state;

                        return match ($value) {
                            'internal' => 'success',
                            'myparts' => 'info',
                            default => 'gray',
                        };
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('შეკვეთები')
                    ->badge()
                    ->icon('heroicon-o-shopping-bag')
                    ->color(fn ($state): string => ((int) $state) > 0 ? 'primary' : 'gray')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('განახლდა')
                    ->since()
                    ->icon('heroicon-o-clock')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('წყარო')
                    ->options([
                        'internal' => 'შიდა',
                        'myparts' => 'MyParts',
                        'other' => 'სხვა',
                    ]),
                TernaryFilter::make('has_orders')
                    ->label('აქვს შეკვეთები')
                    ->queries(
                        true: fn ($query) => $query->has('orders'),
                        false: fn ($query) => $query->doesntHave('orders'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->emptyStateHeading('მონაცემები ვერ მოიძებნა')
            ->defaultSort('updated_at', 'desc')
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
