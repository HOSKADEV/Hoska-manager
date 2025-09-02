<?php

namespace App\Helpers;

use App\Models\Setting;

class CurrencyHelper
{
    public static function convert(float $amount, ?string $fromCurrency, ?string $toCurrency): float
    {
        $usdRate = Setting::get('usd_rate', 140); // 1 USD = 140 DZD
        $eurRate = Setting::get('eur_rate', 150); // 1 EUR = 150 DZD

        // fallback to DZD if null
        $fromCurrency = strtoupper($fromCurrency ?? 'DZD');
        $toCurrency   = strtoupper($toCurrency ?? 'DZD');

        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // to DZD
        switch ($fromCurrency) {
            case 'USD':
                $amount = $amount * $usdRate;
                break;
            case 'EUR':
                $amount = $amount * $eurRate;
                break;
        }

        // from DZD to target
        switch ($toCurrency) {
            case 'USD':
                return $amount / $usdRate;
            case 'EUR':
                return $amount / $eurRate;
            case 'DZD':
                return $amount;
            default:
                return $amount;
        }
    }
}
