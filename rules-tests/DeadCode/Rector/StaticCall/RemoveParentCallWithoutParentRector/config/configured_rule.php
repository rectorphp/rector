<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveParentCallWithoutParentRector::class);
};
