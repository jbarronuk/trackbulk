<?php

return [
    'stripe_portal' => env('STRIPE_PORTAL'),

    'stripe_prices' => [
        'basic_monthly' => env('STRIPE_PRICE_BASIC_MONTHLY'),
        'basic_yearly'  => env('STRIPE_PRICE_BASIC_YEARLY'),
        'pro_monthly'   => env('STRIPE_PRICE_PRO_MONTHLY'),
        'pro_yearly'    => env('STRIPE_PRICE_PRO_YEARLY'),
    ]
];
