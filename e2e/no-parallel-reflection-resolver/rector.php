<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();

    $parameters->set(Option::PARALLEL, false);
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src/',
    ]);

    $services = $rectorConfig->services();
    $services->set(RemoveUnusedPrivatePropertyRector::class);
};

