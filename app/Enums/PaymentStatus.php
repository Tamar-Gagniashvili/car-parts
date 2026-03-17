<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Partial = 'partial';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'გადაუხდელი',
            self::Paid => 'გადახდილი',
            self::Refunded => 'დაბრუნებული',
            self::Partial => 'ნაწილობრივ გადახდილი',
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
            self::Unpaid->value => 'warning',
            self::Paid->value => 'success',
            self::Refunded->value => 'danger',
            self::Partial->value => 'info',
        ];
    }
}
