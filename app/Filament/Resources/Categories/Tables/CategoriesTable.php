<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('დასახელება')
                    ->icon('heroicon-o-tag')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('პროდუქტები')
                    ->badge()
                    ->icon('heroicon-o-cube')
                    ->color(function ($state): string {
                        $count = (int) $state;

                        return match (true) {
                            $count >= 50 => 'success',
                            $count >= 10 => 'info',
                            $count > 0 => 'warning',
                            default => 'gray',
                        };
                    })
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('განახლდა')
                    ->dateTime()
                    ->since()
                    ->icon('heroicon-o-clock')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('has_products')
                    ->label('აქვს პროდუქტები')
                    ->queries(
                        true: fn ($query) => $query->has('products'),
                        false: fn ($query) => $query->doesntHave('products'),
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
