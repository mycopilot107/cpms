<?php

namespace App\Support;

/**
 * Tenant phone numbers are a 10-digit mobile number. A leading 91 on a
 * 12-digit value is the India country code and is dropped before the length
 * check, so an older "+91…" number can still be saved.
 */
class Phone
{
    public static function normalize(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    /**
     * @return list<string>
     */
    public static function rules(): array
    {
        return ['nullable', 'digits:10'];
    }
}
