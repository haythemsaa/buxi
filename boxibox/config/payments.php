<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway used by your application.
    | Supported: "stripe", "paypal", "sepa"
    |
    */

    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure all payment gateways used by your application.
    |
    */

    'gateways' => [
        'stripe' => [
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'eur'),
            'fee_percentage' => 1.4, // 1.4% + 0.25€ per transaction (EU cards)
            'fee_fixed' => 0.25,
        ],

        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'secret' => env('PAYPAL_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // 'sandbox' or 'live'
            'currency' => env('PAYPAL_CURRENCY', 'EUR'),
            'fee_percentage' => 2.49, // 2.49% + 0.35€ per transaction
            'fee_fixed' => 0.35,
        ],

        'sepa' => [
            'creditor_id' => env('SEPA_CREDITOR_ID'),
            'creditor_name' => env('SEPA_CREDITOR_NAME', 'Boxibox'),
            'creditor_iban' => env('SEPA_CREDITOR_IBAN'),
            'creditor_bic' => env('SEPA_CREDITOR_BIC'),
            'currency' => 'EUR',
            'fee_percentage' => 0, // Typically no fees for SEPA
            'fee_fixed' => 0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Strategy
    |--------------------------------------------------------------------------
    |
    | Define which gateway to use as fallback if primary fails
    |
    */

    'fallback' => [
        'enabled' => env('PAYMENT_FALLBACK_ENABLED', true),
        'gateway' => env('PAYMENT_FALLBACK_GATEWAY', 'sepa'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 3D Secure
    |--------------------------------------------------------------------------
    |
    | Enable 3D Secure authentication for card payments
    |
    */

    '3d_secure' => [
        'enabled' => env('PAYMENT_3D_SECURE_ENABLED', true),
        'threshold' => env('PAYMENT_3D_SECURE_THRESHOLD', 30), // Amount in EUR
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | Configure webhook endpoints for payment notifications
    |
    */

    'webhooks' => [
        'stripe' => '/webhooks/stripe',
        'paypal' => '/webhooks/paypal',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Enabled payment methods for customers
    |
    */

    'methods' => [
        'card' => env('PAYMENT_METHOD_CARD', true),
        'paypal' => env('PAYMENT_METHOD_PAYPAL', true),
        'sepa' => env('PAYMENT_METHOD_SEPA', true),
        'apple_pay' => env('PAYMENT_METHOD_APPLE_PAY', true),
        'google_pay' => env('PAYMENT_METHOD_GOOGLE_PAY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Strategy
    |--------------------------------------------------------------------------
    |
    | Configure automatic retry for failed payments
    |
    */

    'retry' => [
        'enabled' => true,
        'max_attempts' => 3,
        'delay_days' => [1, 3, 7], // Retry after 1, 3, and 7 days
    ],
];
