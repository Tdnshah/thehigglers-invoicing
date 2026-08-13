<?php

namespace App\Support;

/**
 * Currency formatting helpers shared by the printed / PDF documents.
 */
class Money
{
    /**
     * Symbols for the currencies the app is likely to invoice in.
     * Anything unknown falls back to the ISO code.
     */
    protected const SYMBOLS = [
        'INR' => '₹',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'AED' => 'AED ',
        'SAR' => 'SAR ',
        'AUD' => 'A$',
        'CAD' => 'C$',
        'SGD' => 'S$',
        'JPY' => '¥',
    ];

    /**
     * Major / minor unit names used when spelling an amount out in words.
     */
    protected const UNITS = [
        'INR' => ['Rupees', 'Paise'],
        'USD' => ['Dollars', 'Cents'],
        'EUR' => ['Euros', 'Cents'],
        'GBP' => ['Pounds', 'Pence'],
        'AED' => ['Dirhams', 'Fils'],
    ];

    public static function symbol(string $currency): string
    {
        return self::SYMBOLS[strtoupper($currency)] ?? (strtoupper($currency) . ' ');
    }

    /**
     * Format an amount without the currency symbol.
     * INR uses the Indian grouping (1,23,456.00), everything else uses thousands.
     */
    public static function format($amount, string $currency = 'INR'): string
    {
        $amount = round((float) $amount, 2);

        if (strtoupper($currency) !== 'INR') {
            return number_format($amount, 2);
        }

        $sign = $amount < 0 ? '-' : '';
        $amount = abs($amount);

        $whole = (int) floor($amount);
        $decimals = str_pad((string) (int) round(($amount - $whole) * 100), 2, '0', STR_PAD_LEFT);
        $whole = (string) $whole;

        if (strlen($whole) > 3) {
            $last3 = substr($whole, -3);
            $rest = substr($whole, 0, -3);
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $whole = $rest . ',' . $last3;
        }

        return $sign . $whole . '.' . $decimals;
    }

    /**
     * Format an amount with its currency symbol, e.g. "₹1,23,456.00".
     */
    public static function formatWithSymbol($amount, string $currency = 'INR'): string
    {
        return self::symbol($currency) . self::format($amount, $currency);
    }

    /**
     * Spell an amount out in words, e.g.
     * "Rupees Five Lakh Sixty Six Thousand Eight Hundred Eighteen and Fifty Paise Only".
     */
    public static function inWords($amount, string $currency = 'INR'): string
    {
        $currency = strtoupper($currency);
        $amount = round((float) $amount, 2);
        $negative = $amount < 0;
        $amount = abs($amount);

        $whole = (int) floor($amount);
        $fraction = (int) round(($amount - $whole) * 100);

        [$major, $minor] = self::UNITS[$currency] ?? [$currency, 'Cents'];

        $words = $currency === 'INR'
            ? self::indianWords($whole)
            : self::internationalWords($whole);

        $out = trim($major . ' ' . $words);

        if ($fraction > 0) {
            $fractionWords = $currency === 'INR'
                ? self::indianWords($fraction)
                : self::internationalWords($fraction);
            $out .= ' and ' . $fractionWords . ' ' . $minor;
        }

        return ($negative ? 'Minus ' : '') . $out . ' Only';
    }

    /**
     * Indian numbering system: crore / lakh / thousand / hundred.
     */
    protected static function indianWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $parts = [];

        foreach ([10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand', 100 => 'Hundred'] as $divisor => $label) {
            if ($number >= $divisor) {
                $count = intdiv($number, $divisor);
                $number %= $divisor;
                $parts[] = ($divisor >= 10000000 ? self::indianWords($count) : self::belowHundred($count)) . ' ' . $label;
            }
        }

        if ($number > 0) {
            $parts[] = self::belowHundred($number);
        }

        return implode(' ', $parts);
    }

    /**
     * International numbering system: billion / million / thousand / hundred.
     */
    protected static function internationalWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $parts = [];

        foreach ([1000000000 => 'Billion', 1000000 => 'Million', 1000 => 'Thousand', 100 => 'Hundred'] as $divisor => $label) {
            if ($number >= $divisor) {
                $count = intdiv($number, $divisor);
                $number %= $divisor;
                $parts[] = ($divisor >= 1000 ? self::internationalWords($count) : self::belowHundred($count)) . ' ' . $label;
            }
        }

        if ($number > 0) {
            $parts[] = self::belowHundred($number);
        }

        return implode(' ', $parts);
    }

    protected static function belowHundred(int $number): string
    {
        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen',
        ];
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        $word = $tens[intdiv($number, 10)];
        $remainder = $number % 10;

        return $remainder ? $word . ' ' . $ones[$remainder] : $word;
    }
}
