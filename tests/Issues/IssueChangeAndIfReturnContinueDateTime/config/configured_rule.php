<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector;
use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DateTimeToDateTimeInterfaceRector::class);
    $rectorConfig->rule(ChangeAndIfToEarlyReturnRector::class);
    $rectorConfig->rule(ChangeOrIfContinueToMultiContinueRector::class);
};
