<?php

namespace App\Enums;

enum SaleChannel: string
{
    case Internal = 'internal';
    case MyParts = 'myparts';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'შიდა გაყიდვა',
            self::MyParts => 'MyParts.ge',
            self::Other => 'სხვა',
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
