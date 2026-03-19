<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\SaleChannel;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Services\Inventory\InventoryService;
use App\Services\Orders\OrderService;
use App\Support\MoneyFormatter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primary_image_url')
                    ->label('სურათი')
                    ->square()
                    ->size(48)
                    ->toggleable(),
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
                    ->icon('heroicon-o-tag')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('sale_price')
                    ->label('ფასი')
                    ->formatStateUsing(fn ($state, $record) => MoneyFormatter::format(
                        amount: $state !== null ? (float) $state : null,
                        currency: Currency::fromId($record->currency_id),
                    ))
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('currency_id')
                    ->label('ვალუტა')
                    ->formatStateUsing(fn ($state) => Currency::fromId((int) $state)?->label() ?? '—')
                    ->badge()
                    ->color(fn ($state): string => match (Currency::fromId((int) $state)?->value) {
                        'GEL' => 'success',
                        'USD' => 'info',
                        'EUR' => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quantity_in_stock')
                    ->label('მარაგი')
                    ->badge()
                    ->icon('heroicon-o-cube')
                    ->color(function ($state): string {
                        $qty = (int) $state;

                        return match (true) {
                            $qty <= 0 => 'danger',
                            $qty <= 5 => 'warning',
                            default => 'success',
                        };
                    })
                    ->sortable()
                    ->alignEnd(),
                IconColumn::make('is_active')
                    ->label('სტატუსი')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
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
                SelectFilter::make('currency_id')
                    ->label('ვალუტა')
                    ->options(Currency::idOptions()),
                TernaryFilter::make('is_active')
                    ->label('აქტიურობა'),
            ])
            ->emptyStateHeading('მონაცემები ვერ მოიძებნა')
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                Action::make('quick_order')
                    ->label('სწრაფი შეკვეთა')
                    ->icon('heroicon-o-shopping-bag')
                    ->iconButton()
                    ->fillForm(fn (Product $record) => [
                        'unit_price' => (float) ($record->sale_price ?? 0),
                        'quantity' => 1,
                        'sale_channel' => SaleChannel::Internal->value,
                        'mark_completed' => true,
                    ])
                    ->form([
                        Grid::make(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('არსებული კლიენტი')
                                    ->options(fn () => Customer::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->placeholder('აირჩიეთ კლიენტი ან შექმენით ახალი ქვემოთ'),
                                TextInput::make('customer_name')
                                    ->label('ახალი კლიენტი - სახელი')
                                    ->maxLength(255),
                                TextInput::make('customer_phone')
                                    ->label('ახალი კლიენტი - ტელეფონი')
                                    ->maxLength(255),
                                TextInput::make('quantity')
                                    ->label('რაოდენობა')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1)
                                    ->helperText(fn (Product $record) => 'შიდა მარაგი: '.(int) $record->quantity_in_stock)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->label('ერთეულის ფასი')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix(fn (Product $record) => Currency::fromId($record->currency_id)?->value ?? 'GEL')
                                    ->required(),
                                Select::make('sale_channel')
                                    ->label('გაყიდვის არხი')
                                    ->options(SaleChannel::options())
                                    ->required(),
                                Checkbox::make('mark_completed')
                                    ->label('დასრულებულად მონიშვნა (მარაგის ავტომატური ჩამოჭრა)')
                                    ->default(true),
                                Textarea::make('notes')
                                    ->label('შენიშვნა')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->maxLength(1000),
                            ]),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $requestedQty = (int) $data['quantity'];
                        $markCompleted = (bool) ($data['mark_completed'] ?? false);

                        $record->refresh();
                        $availableQty = (int) $record->quantity_in_stock;

                        if ($markCompleted && $requestedQty > $availableQty) {
                            Notification::make()
                                ->title('შიდა მარაგი საკმარისი არ არის')
                                ->body("ხელმისაწვდომია: {$availableQty}, მოთხოვნილია: {$requestedQty}")
                                ->danger()
                                ->send();

                            return;
                        }

                        $customerId = $data['customer_id'] ?? null;

                        if (! $customerId && ! empty($data['customer_name'])) {
                            $customer = Customer::query()->create([
                                'name' => $data['customer_name'],
                                'phone' => $data['customer_phone'] ?? null,
                                'source' => 'internal',
                            ]);
                            $customerId = $customer->id;
                        }

                        /** @var OrderService $orders */
                        $orders = app(OrderService::class);
                        try {
                            $order = $orders->createOrder(
                                data: [
                                    'customer_id' => $customerId,
                                    'sale_channel' => $data['sale_channel'],
                                    'status' => ($markCompleted ? OrderStatus::Completed : OrderStatus::Draft)->value,
                                    'sold_at' => now(),
                                    'notes' => $data['notes'] ?? null,
                                ],
                                items: [[
                                    'product_id' => $record->id,
                                    'quantity' => $requestedQty,
                                    'unit_price' => (float) $data['unit_price'],
                                ]],
                                user: Auth::user(),
                            );
                        } catch (\RuntimeException $e) {
                            $record->refresh();
                            Notification::make()
                                ->title('შიდა მარაგი საკმარისი არ არის')
                                ->body("ხელმისაწვდომია: {$record->quantity_in_stock}, მოთხოვნილია: {$requestedQty}")
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('შეკვეთა წარმატებით შეიქმნა: '.$order->order_number)
                            ->success()
                            ->send();
                    }),
                Action::make('adjust_stock')
                    ->label('მარაგის კორექტირება')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->iconButton()
                    ->fillForm(fn (Product $record) => ['target_quantity' => $record->quantity_in_stock])
                    ->form([
                        TextInput::make('target_quantity')
                            ->label('ახალი რაოდენობა')
                            ->numeric()
                            ->required(),
                        Checkbox::make('allow_negative')
                            ->label('უარყოფითი მარაგის დაშვება')
                            ->helperText('ჩვეულებრივ არ არის რეკომენდებული.'),
                        TextInput::make('note')
                            ->label('შენიშვნა')
                            ->maxLength(255),
                    ])
                    ->action(function (Product $record, array $data): void {
                        /** @var InventoryService $inventory */
                        $inventory = app(InventoryService::class);

                        $inventory->adjustTo(
                            product: $record,
                            targetQuantity: (int) $data['target_quantity'],
                            note: $data['note'] ?? null,
                            createdBy: Auth::user(),
                            reference: null,
                            allowNegative: (bool) ($data['allow_negative'] ?? false),
                        );

                        Notification::make()
                            ->title('მარაგი განახლდა')
                            ->success()
                            ->send();
                    }),
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
