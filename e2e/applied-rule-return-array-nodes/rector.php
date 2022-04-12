<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
    ]);

    $services = $rectorConfig->services();
    $services->set(RemoveAlwaysElseRector::class);
    $services->set(RemoveUnusedPrivateMethodRector::class);
};

