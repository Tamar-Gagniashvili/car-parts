<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('დასახელება')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('კატეგორია')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('sale_price')
                    ->money('GEL')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quantity_in_stock')
                    ->label('მარაგი')
                    ->sortable()
                    ->alignEnd(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('განახლდა')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('კატეგორია')
                    ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                Filter::make('active')
                    ->label('მხოლოდ აქტიური')
                    ->query(fn ($query) => $query->where('is_active', true)),
                Filter::make('out_of_stock')
                    ->label('მარაგი არ არის')
                    ->query(fn ($query) => $query->where('quantity_in_stock', '<=', 0)),
                Filter::make('has_marketplace_listing')
                    ->label('აქვს MyParts განცხადება')
                    ->query(fn ($query) => $query->whereHas('marketplaceListings')),
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
