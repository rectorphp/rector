<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Tests\Transform\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;
use Rector\Transform\Rector\Class_\AddInterfaceByTraitRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(AddInterfaceByTraitRector::class)
        ->configure([
            SomeTrait::class => SomeInterface::class,
        ]);
};
