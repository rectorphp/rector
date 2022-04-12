<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(AddMethodParentCallRector::class)
        ->configure([
            ParentClassWithNewConstructor::class => '__construct',
        ]);
};
