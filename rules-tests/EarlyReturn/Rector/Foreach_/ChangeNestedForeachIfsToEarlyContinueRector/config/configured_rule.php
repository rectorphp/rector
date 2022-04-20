<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ChangeNestedForeachIfsToEarlyContinueRector::class);
};
