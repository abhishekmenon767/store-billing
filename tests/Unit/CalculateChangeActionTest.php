<?php

use App\Actions\CalculateChangeAction;

it('splits the change into notes and coins', function () {
    $result = (new CalculateChangeAction)->execute(73);

    expect($result['breakdown'])->toBe([
        ['denomination' => 50, 'count' => 1],
        ['denomination' => 20, 'count' => 1],
        ['denomination' => 2, 'count' => 1],
        ['denomination' => 1, 'count' => 1],
    ]);
});
