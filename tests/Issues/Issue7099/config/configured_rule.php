<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->set(RemoveDeadInstanceOfRector::class);
    $services->set(BooleanInTernaryOperatorRuleFixerRector::class)
        ->configure([
            BooleanInTernaryOperatorRuleFixerRector::TREAT_AS_NON_EMPTY => false,
        ]);
    $services->set(DisallowedEmptyRuleFixerRector::class)
        ->configure([
            DisallowedEmptyRuleFixerRector::TREAT_AS_NON_EMPTY => false,
        ]);
};
