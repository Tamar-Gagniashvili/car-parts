<?php

namespace App\Enums;

enum MarketplaceChannel: string
{
    case MyParts = 'myparts';

    public function label(): string
    {
        return match ($this) {
            self::MyParts => 'MyParts.ge',
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
}
