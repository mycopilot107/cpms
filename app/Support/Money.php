<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;

/**
 * Formats a company's own project money (budget, BOQ, expenses, payments) in
 * the currency that company chose. Not used for the Best CPMS subscription
 * price — see App\Support\Currency for that.
 */
class Money
{
    /** Currency code for the company, or the signed-in user's company. */
    public static function code(?Company $company = null): string
    {
        $company ??= Auth::user()?->company;
        $code = strtoupper((string) ($company?->currency ?? ''));

        return array_key_exists($code, static::currencies())
            ? $code
            : (string) config('regional.default_currency', 'INR');
    }

    public static function symbol(?Company $company = null): string
    {
        return (string) (static::currencies()[static::code($company)]['symbol'] ?? '');
    }

    /** BCP 47 locale for client-side number formatting, such as "en-IN". */
    public static function locale(?Company $company = null): string
    {
        return (string) (static::currencies()[static::code($company)]['locale'] ?? 'en-US');
    }

    /** "₹1,250.00", "$1,250.00", "AED 1,250.00". */
    public static function format(int|float|string|null $amount, ?Company $company = null): string
    {
        $value = (float) ($amount ?? 0);
        $sign = $value < 0 ? '-' : '';

        return $sign.static::symbol($company).number_format(abs($value), 2);
    }

    /**
     * @return array<string, array{label: string, symbol: string, locale: string}>
     */
    public static function currencies(): array
    {
        return (array) config('regional.currencies', []);
    }

    /**
     * @return array<string, string>
     */
    public static function countries(): array
    {
        return (array) config('regional.countries', []);
    }
}
