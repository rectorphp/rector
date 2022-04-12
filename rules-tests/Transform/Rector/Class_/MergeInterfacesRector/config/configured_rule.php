<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\Class_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Tests\Transform\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;
use Rector\Transform\Rector\Class_\MergeInterfacesRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(MergeInterfacesRector::class)
        ->configure([
            SomeOldInterface::class => SomeInterface::class,
        ]);
};
