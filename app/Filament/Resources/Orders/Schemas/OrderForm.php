<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SaleChannel;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('შეკვეთა')
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('კლიენტი')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('order_number')
                            ->label('შეკვეთის ნომერი')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('status')
                            ->label('სტატუსი')
                            ->options(OrderStatus::options())
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('payment_status')
                            ->label('გადახდის სტატუსი')
                            ->options(PaymentStatus::options())
                            ->searchable()
                            ->preload(),
                        Select::make('sale_channel')
                            ->label('გაყიდვის არხი')
                            ->options(SaleChannel::options())
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('sold_at')
                            ->label('გაყიდვის თარიღი'),
                        Textarea::make('notes')
                            ->label('შენიშვნები')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('ჯამები')
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')->label('შუალედური ჯამი')->numeric()->required()->default(0),
                        TextInput::make('discount')->label('ფასდაკლება')->numeric()->required()->default(0),
                        TextInput::make('total')->label('სულ')->numeric()->required()->default(0),
                    ]),
            ]);
    }
}
