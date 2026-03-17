<?php

namespace App\Enums;

/**
 * Sync state for marketplace listings (import/push jobs).
 *
 * This is intentionally "operational" state (not the external marketplace status).
 */
enum MarketplaceSyncStatus: string
{
    case Never = 'never';
    case Pending = 'pending';
    case Syncing = 'syncing';
    case Synced = 'synced';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Never => 'არასოდეს დასინქრონებულა',
            self::Pending => 'მოლოდინში',
            self::Syncing => 'სინქრონიზაცია მიმდინარეობს',
            self::Synced => 'დასინქრონებულია',
            self::Failed => 'შეცდომა',
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
            self::Never->value => 'gray',
            self::Pending->value => 'warning',
            self::Syncing->value => 'info',
            self::Synced->value => 'success',
            self::Failed->value => 'danger',
        ];
    }
}
