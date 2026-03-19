<?php

namespace App\Filament\Resources\MarketplaceListings\Tables;

use App\Enums\Currency;
use App\Enums\MarketplaceChannel;
use App\Enums\OrderStatus;
use App\Enums\SaleChannel;
use App\Models\Customer;
use App\Models\Product;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MarketplaceListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('external_thumb_url')
                    ->label('სურათი')
                    ->square()
                    ->size(64),
                TextColumn::make('channel')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $value = $state instanceof MarketplaceChannel ? $state->value : (string) $state;

                        return MarketplaceChannel::from($value)->label();
                    })
                    ->icon(function ($state): string {
                        $value = $state instanceof MarketplaceChannel ? $state->value : (string) $state;

                        return match ($value) {
                            MarketplaceChannel::MyParts->value => 'heroicon-o-globe-alt',
                            default => 'heroicon-o-link',
                        };
                    })
                    ->color(function ($state): string {
                        $value = $state instanceof MarketplaceChannel ? $state->value : (string) $state;

                        return match ($value) {
                            MarketplaceChannel::MyParts->value => 'info',
                            default => 'gray',
                        };
                    })
                    ->label('არხი')
                    ->sortable(),
                TextColumn::make('external_id')
                    ->label('გარე ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('external_url')
                    ->label('MyParts ბმული')
                    ->formatStateUsing(fn ($state) => $state)
                    ->url(fn ($record) => $record->external_url, shouldOpenInNewTab: true)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('product.name')
                    ->label('შიდა პროდუქტი')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('product.quantity_in_stock')
                    ->label('მარაგი')
                    ->numeric()
                    ->badge()
                    ->icon('heroicon-o-cube')
                    ->color(function ($state): string {
                        if ($state === null) {
                            return 'gray';
                        }

                        $qty = (int) $state;

                        return match (true) {
                            $qty <= 0 => 'danger',
                            $qty <= 5 => 'warning',
                            default => 'success',
                        };
                    })
                    ->placeholder('—')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('external_price')
                    ->label('ფასი')
                    ->formatStateUsing(fn ($state, $record) => MoneyFormatter::format(
                        amount: $state !== null ? (float) $state : null,
                        currency: Currency::fromId($record->external_currency_id),
                    ))
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('external_quantity')
                    ->label('რაოდენობა')
                    ->badge()
                    ->color(fn ($state): string => ((int) $state) > 0 ? 'info' : 'gray')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('views')
                    ->label('ნახვები')
                    ->icon('heroicon-o-eye')
                    ->color(function ($state): string {
                        $views = (int) $state;

                        return match (true) {
                            $views >= 1000 => 'success',
                            $views >= 200 => 'info',
                            $views > 0 => 'warning',
                            default => 'gray',
                        };
                    })
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('create_date')
                    ->label('შექმნილია')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('update_date')
                    ->label('განახლდა')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('end_date')
                    ->label('დასრულდება')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->since()
                    ->label('სინქრონიზაცია')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('არხი')
                    ->options(MarketplaceChannel::options()),
                SelectFilter::make('product_id')
                    ->label('მიბმულია პროდუქტზე')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_linked')
                    ->label('მიბმულობა')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('product_id'),
                        false: fn ($query) => $query->whereNull('product_id'),
                        blank: fn ($query) => $query,
                    ),
                Filter::make('low_internal_stock')
                    ->label('დაბალი შიდა მარაგი (<=5)')
                    ->query(fn ($query) => $query->whereHas('product', fn ($q) => $q->where('quantity_in_stock', '<=', 5))),
            ])
            ->emptyStateHeading('მონაცემები ვერ მოიძებნა')
            ->defaultSort('update_date', 'desc')
            ->recordActions([
                Action::make('quick_order')
                    ->label('სწრაფი შეკვეთა')
                    ->icon('heroicon-o-shopping-bag')
                    ->iconButton()
                    ->fillForm(fn ($record) => [
                        'product_id' => $record->product_id,
                        'unit_price' => $record->external_price !== null ? (float) $record->external_price : null,
                        'quantity' => 1,
                        'sale_channel' => SaleChannel::MyParts->value,
                        'mark_completed' => true,
                    ])
                    ->form([
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label('პროდუქტი')
                                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->live()
                                    ->searchable()
                                    ->required(),
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
                                    ->helperText(function (callable $get): string {
                                        $productId = (int) ($get('product_id') ?? 0);
                                        $stock = $productId > 0
                                            ? (int) (Product::query()->whereKey($productId)->value('quantity_in_stock') ?? 0)
                                            : 0;

                                        return "შიდა მარაგი: {$stock} (განცხადების რაოდენობისგან დამოუკიდებლად)";
                                    })
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->label('ერთეულის ფასი')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix(fn ($record) => Currency::fromId($record->external_currency_id)?->value ?? 'GEL')
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
                    ->action(function ($record, array $data): void {
                        $requestedQty = (int) $data['quantity'];
                        $markCompleted = (bool) ($data['mark_completed'] ?? false);

                        /** @var Product|null $product */
                        $product = Product::query()->find((int) $data['product_id']);
                        if (! $product) {
                            Notification::make()
                                ->title('პროდუქტი ვერ მოიძებნა')
                                ->danger()
                                ->send();

                            return;
                        }

                        $availableQty = (int) $product->quantity_in_stock;
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
                                'source' => 'myparts',
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
                                    'product_id' => (int) $data['product_id'],
                                    'quantity' => $requestedQty,
                                    'unit_price' => (float) $data['unit_price'],
                                ]],
                                user: Auth::user(),
                            );
                        } catch (\RuntimeException $e) {
                            $product->refresh();
                            Notification::make()
                                ->title('შიდა მარაგი საკმარისი არ არის')
                                ->body("ხელმისაწვდომია: {$product->quantity_in_stock}, მოთხოვნილია: {$requestedQty}")
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('შეკვეთა წარმატებით შეიქმნა: '.$order->order_number)
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
