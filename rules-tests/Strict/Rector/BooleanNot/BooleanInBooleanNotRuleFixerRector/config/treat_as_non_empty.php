<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Strict\Rector\BooleanNot\BooleanInBooleanNotRuleFixerRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(BooleanInBooleanNotRuleFixerRector::class)
        ->configure([
            BooleanInBooleanNotRuleFixerRector::TREAT_AS_NON_EMPTY => true,
        ]);
};
