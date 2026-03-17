<?php

use Carbon\Carbon;

if (! function_exists('format_currency')) {
    /**
     * Format a monetary amount with the correct currency symbol and locale.
     *
     * BOB → "Bs. 1.234.567,00"
     * USD → "$ 1,234.567"  (en_US style)
     * COP → "$ 1.234.567"  (es_CO style)
     */
    function format_currency(float $amount, string $currency = 'BOB'): string
    {
        return match ($currency) {
            'BOB' => 'Bs. ' . number_format($amount, 2, ',', '.'),
            'USD' => '$ ' . number_format($amount, 2, '.', ','),
            'COP' => '$ ' . number_format($amount, 0, ',', '.'),
            default => number_format($amount, 2, ',', '.') . ' ' . $currency,
        };
    }
}

if (! function_exists('user_timezone')) {
    /**
     * Return the authenticated user's timezone, falling back to app default.
     */
    function user_timezone(): string
    {
        if (auth()->check()) {
            return auth()->user()->timezone ?? config('app.timezone', 'America/La_Paz');
        }
        return config('app.timezone', 'America/La_Paz');
    }
}

if (! function_exists('format_date')) {
    /**
     * Format a date in DD/MM/YYYY, converting to user's timezone.
     */
    function format_date(Carbon|string|null $date, ?string $timezone = null): string
    {
        if (is_null($date)) {
            return '-';
        }
        $tz = $timezone ?? user_timezone();
        return Carbon::parse($date)->setTimezone($tz)->format('d/m/Y');
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Format a datetime in DD/MM/YYYY HH:MM, converting to user's timezone.
     */
    function format_datetime(Carbon|string|null $datetime, ?string $timezone = null): string
    {
        if (is_null($datetime)) {
            return '-';
        }
        $tz = $timezone ?? user_timezone();
        return Carbon::parse($datetime)->setTimezone($tz)->format('d/m/Y H:i');
    }
}
