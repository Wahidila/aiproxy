<?php

return [
    'base_url' => env('ENOWXAI_BASE_URL', 'http://43.133.141.45:1434/v1'),
    'api_key' => env('ENOWXAI_API_KEY', ''),
    'dashboard_url' => env('ENOWXAI_DASHBOARD_URL', 'http://43.133.141.45:1435'),

    // Wallet system
    'free_credit_amount' => (float) env('FREE_CREDIT_AMOUNT', 100000), // Rp 100.000 free trial
    'min_topup_amount' => (int) env('MIN_TOPUP_AMOUNT', 10000), // Rp 10.000 minimum topup
];
