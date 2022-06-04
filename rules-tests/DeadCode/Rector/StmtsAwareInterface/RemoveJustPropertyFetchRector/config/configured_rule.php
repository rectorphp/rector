<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveJustPropertyFetchRector::class);
};
