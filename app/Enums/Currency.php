<?php

namespace App\Enums;

enum Currency: string
{
    case GEL = 'GEL';
    case USD = 'USD';
    case EUR = 'EUR';

    public function label(): string
    {
        return match ($this) {
            self::GEL => '₾ GEL',
            self::USD => '$ USD',
            self::EUR => '€ EUR',
        };
    }

    /**
     * Map external numeric currency IDs to ISO codes.
     * Adjust as you learn exact MyParts mappings.
     */
    public static function fromId(?int $id): ?self
    {
        return match ($id) {
            1 => self::GEL,
            2 => self::USD,
            3 => self::USD,
            4 => self::EUR,
            default => null,
        };
    }

    /**
     * Filament-friendly options: [id => label]
     *
     * @return array<int, string>
     */
    public static function idOptions(): array
    {
        return [
            1 => self::GEL->label(),
            2 => self::USD->label(),
            4 => self::EUR->label(),
        ];
    }
}
