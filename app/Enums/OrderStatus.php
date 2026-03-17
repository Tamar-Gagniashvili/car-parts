<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
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
            self::Draft->value => 'gray',
            self::Confirmed->value => 'info',
            self::Completed->value => 'success',
            self::Cancelled->value => 'danger',
        ];
    }
}
