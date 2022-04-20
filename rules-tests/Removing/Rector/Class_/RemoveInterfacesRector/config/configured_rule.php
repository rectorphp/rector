<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\Class_\RemoveInterfacesRector;
use Rector\Tests\Removing\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RemoveInterfacesRector::class, [SomeInterface::class]);
};
