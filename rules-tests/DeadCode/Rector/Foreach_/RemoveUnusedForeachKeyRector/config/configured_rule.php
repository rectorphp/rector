<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveUnusedForeachKeyRector::class);
};
