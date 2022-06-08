<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector;
use Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveJustPropertyFetchForAssignRector::class);
    $rectorConfig->rule(SimplifyForeachInstanceOfRector::class);
};
