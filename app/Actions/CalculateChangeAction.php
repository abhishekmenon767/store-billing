<?php

namespace App\Actions;

class CalculateChangeAction
{
    public function execute(float $change, ?array $denominations = null): array
    {
        $denominations ??= config('store.cash_denominations');
        rsort($denominations);

        $paise = (int) round($change * 100);
        $rupees = intdiv($paise, 100);
        $breakdown = [];

        foreach ($denominations as $denomination) {
            if ($count = intdiv($rupees, $denomination)) {
                $breakdown[] = ['denomination' => $denomination, 'count' => $count];
                $rupees %= $denomination;
            }
        }

        return [
            'amount' => number_format($paise / 100, 2, '.', ''),
            'breakdown' => $breakdown,
            'remainder' => number_format(($paise % 100 + $rupees * 100) / 100, 2, '.', ''),
        ];
    }
}
