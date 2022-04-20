<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDeadInstanceOfRector::class);
    $rectorConfig
        ->ruleWithConfiguration(BooleanInTernaryOperatorRuleFixerRector::class, [
            BooleanInTernaryOperatorRuleFixerRector::TREAT_AS_NON_EMPTY => false,
        ]);
    $rectorConfig
        ->ruleWithConfiguration(DisallowedEmptyRuleFixerRector::class, [
            DisallowedEmptyRuleFixerRector::TREAT_AS_NON_EMPTY => false,
        ]);
};
