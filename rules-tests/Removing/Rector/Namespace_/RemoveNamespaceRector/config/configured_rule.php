<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\Namespace_\RemoveNamespaceRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RemoveNamespaceRector::class)
        ->configure(['App']);
};
