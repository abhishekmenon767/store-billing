<?php

return [

    'low_stock_threshold' => (int) env('STORE_LOW_STOCK_THRESHOLD', 10),

    'currency_symbol' => env('STORE_CURRENCY_SYMBOL', '₹'),

    'cash_denominations' => [500, 200, 100, 50, 20, 10, 5, 2, 1],

];
