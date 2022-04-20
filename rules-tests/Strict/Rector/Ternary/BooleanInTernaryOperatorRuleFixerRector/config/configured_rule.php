<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(BooleanInTernaryOperatorRuleFixerRector::class);
};
