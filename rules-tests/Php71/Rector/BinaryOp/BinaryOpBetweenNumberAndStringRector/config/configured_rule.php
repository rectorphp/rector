<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(BinaryOpBetweenNumberAndStringRector::class);
};
