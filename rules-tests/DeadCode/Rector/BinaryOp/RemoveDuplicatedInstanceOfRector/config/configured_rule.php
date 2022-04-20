<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDuplicatedInstanceOfRector::class);
};
