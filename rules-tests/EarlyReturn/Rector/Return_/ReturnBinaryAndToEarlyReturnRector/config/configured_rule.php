<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReturnBinaryAndToEarlyReturnRector::class);
};
