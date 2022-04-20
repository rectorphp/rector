<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeArbitraryExpressionArgsToEmptyAndIssetRector::class);
};
