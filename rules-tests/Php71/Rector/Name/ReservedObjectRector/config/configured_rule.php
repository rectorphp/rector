<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\Name\ReservedObjectRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ReservedObjectRector::class)
        ->configure([
            'ReservedObject' => 'SmartObject',
            'Object' => 'AnotherSmartObject',
        ]);
};
