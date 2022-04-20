<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeTrailingCommasInFunctionCallsRector::class);
};
