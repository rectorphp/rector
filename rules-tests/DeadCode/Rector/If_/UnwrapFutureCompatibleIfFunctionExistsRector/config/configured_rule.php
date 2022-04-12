<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(UnwrapFutureCompatibleIfFunctionExistsRector::class);
};
