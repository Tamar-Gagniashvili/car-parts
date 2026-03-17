<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
    case Reserve = 'reserve';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Stock In',
            self::Out => 'Stock Out',
            self::Sale => 'Sale',
            self::Adjustment => 'Adjustment',
            self::Reserve => 'Reserve',
            self::Return => 'Return',
        };
    }

    /**
     * Filament-friendly options: [value => label]
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Filament badge colors (for e.g. TextColumn::badge()->color()).
     *
     * @return array<string, string>
     */
    public static function colors(): array
    {
        return [
            self::In->value => 'success',
            self::Out->value => 'warning',
            self::Sale->value => 'primary',
            self::Adjustment->value => 'gray',
            self::Reserve->value => 'info',
            self::Return->value => 'success',
        ];
    }
}
