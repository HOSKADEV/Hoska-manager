<?php

namespace App\Http\Controllers;

use App\Models\Setting;

abstract class Controller {
    function getCurrencySymbol(string $currencyCode = 'USD'): string {
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'DZD' => 'DZ',
        ];

        return $currencySymbols[strtoupper($currencyCode)] ?? '$'; // Default to $ if currency not found
    }

    /**
     * Convert amount between currencies (USD, EUR, DZD)
     *
     * @param float $amount The amount to convert
     * @param string $fromCurrency The source currency code (USD, EUR, DZD)
     * @param string $toCurrency The target currency code (USD, EUR, DZD)
     * @return float The converted amount
     */
    public function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
    {
        // Get exchange rates from settings with defaults
        $usdRate = Setting::get('usd_rate', 140); // 1 USD = 140 DZD
        $eurRate = Setting::get('eur_rate', 150); // 1 EUR = 150 DZD

        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);

        // If currencies are the same, return the amount as is
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Convert everything to DZD first
        $amountInDZD = $amount;
        switch ($fromCurrency) {
            case 'USD':
                $amountInDZD = $amount * $usdRate;
                break;
            case 'EUR':
                $amountInDZD = $amount * $eurRate;
                break;
            // DZD remains the same
        }

        // Convert from DZD to target currency
        switch ($toCurrency) {
            case 'USD':
                return $amountInDZD / $usdRate;
            case 'EUR':
                return $amountInDZD / $eurRate;
            case 'DZD':
                return $amountInDZD;
            default:
                return $amountInDZD;
        }
    }


}
