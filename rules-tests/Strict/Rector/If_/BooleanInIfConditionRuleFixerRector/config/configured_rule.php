<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Strict\Rector\If_\BooleanInIfConditionRuleFixerRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(BooleanInIfConditionRuleFixerRector::class);
};
