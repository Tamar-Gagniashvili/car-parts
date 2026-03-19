<?php

namespace App\Support;

use App\Enums\Currency;
use NumberFormatter;

class MoneyFormatter
{
    public static function format(?float $amount, ?Currency $currency): ?string
    {
        if ($amount === null) {
            return null;
        }

        $currency ??= Currency::GEL;

        $formatter = new NumberFormatter('ka_GE', NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount, $currency->value) ?: sprintf('%.2f %s', $amount, $currency->value);
    }
}
