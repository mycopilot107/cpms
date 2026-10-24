<?php

/*
|--------------------------------------------------------------------------
| Company country and workspace currency
|--------------------------------------------------------------------------
| A company picks its country and the currency its own project money is
| recorded in (budget, BOQ, expenses, payments, reports). This is separate
| from config/billing.php, which is only about paying for Best CPMS.
|
| Amounts are stored as entered. Changing a company's currency changes the
| symbol shown, not the numbers.
|
| Keep this file cacheable by `php artisan config:cache` — scalars and arrays
| only.
*/

return [

    'default_country' => 'India',

    'default_currency' => 'INR',

    // Country => the currency a company there usually records costs in.
    'countries' => [
        'India' => 'INR',
        'United States' => 'USD',
        'United Kingdom' => 'GBP',
        'United Arab Emirates' => 'AED',
        'Saudi Arabia' => 'SAR',
        'Qatar' => 'QAR',
        'Oman' => 'OMR',
        'Kuwait' => 'KWD',
        'Bahrain' => 'BHD',
        'Canada' => 'CAD',
        'Australia' => 'AUD',
        'New Zealand' => 'NZD',
        'Singapore' => 'SGD',
        'Malaysia' => 'MYR',
        'South Africa' => 'ZAR',
        'Kenya' => 'KES',
        'Nigeria' => 'NGN',
        'Nepal' => 'NPR',
        'Sri Lanka' => 'LKR',
        'Bangladesh' => 'BDT',
        'Ireland' => 'EUR',
        'Germany' => 'EUR',
        'France' => 'EUR',
        'Netherlands' => 'EUR',
        'Other' => 'USD',
    ],

    // ISO 4217 code => how amounts are shown.
    'currencies' => [
        'INR' => ['label' => 'Indian Rupee', 'symbol' => '₹', 'locale' => 'en-IN'],
        'USD' => ['label' => 'US Dollar', 'symbol' => '$', 'locale' => 'en-US'],
        'GBP' => ['label' => 'British Pound', 'symbol' => '£', 'locale' => 'en-GB'],
        'EUR' => ['label' => 'Euro', 'symbol' => '€', 'locale' => 'en-IE'],
        'AED' => ['label' => 'UAE Dirham', 'symbol' => 'AED ', 'locale' => 'en-AE'],
        'SAR' => ['label' => 'Saudi Riyal', 'symbol' => 'SAR ', 'locale' => 'en-SA'],
        'QAR' => ['label' => 'Qatari Riyal', 'symbol' => 'QAR ', 'locale' => 'en-QA'],
        'OMR' => ['label' => 'Omani Rial', 'symbol' => 'OMR ', 'locale' => 'en-OM'],
        'KWD' => ['label' => 'Kuwaiti Dinar', 'symbol' => 'KWD ', 'locale' => 'en-KW'],
        'BHD' => ['label' => 'Bahraini Dinar', 'symbol' => 'BHD ', 'locale' => 'en-BH'],
        'CAD' => ['label' => 'Canadian Dollar', 'symbol' => 'C$', 'locale' => 'en-CA'],
        'AUD' => ['label' => 'Australian Dollar', 'symbol' => 'A$', 'locale' => 'en-AU'],
        'NZD' => ['label' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'locale' => 'en-NZ'],
        'SGD' => ['label' => 'Singapore Dollar', 'symbol' => 'S$', 'locale' => 'en-SG'],
        'MYR' => ['label' => 'Malaysian Ringgit', 'symbol' => 'RM', 'locale' => 'en-MY'],
        'ZAR' => ['label' => 'South African Rand', 'symbol' => 'R', 'locale' => 'en-ZA'],
        'KES' => ['label' => 'Kenyan Shilling', 'symbol' => 'KSh ', 'locale' => 'en-KE'],
        'NGN' => ['label' => 'Nigerian Naira', 'symbol' => '₦', 'locale' => 'en-NG'],
        'NPR' => ['label' => 'Nepalese Rupee', 'symbol' => 'Rs ', 'locale' => 'en-NP'],
        'LKR' => ['label' => 'Sri Lankan Rupee', 'symbol' => 'Rs ', 'locale' => 'en-LK'],
        'BDT' => ['label' => 'Bangladeshi Taka', 'symbol' => '৳', 'locale' => 'en-BD'],
    ],

];
