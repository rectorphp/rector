<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php72\Rector\While_\WhileEachToForeachRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(WhileEachToForeachRector::class);
};
