<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(AddMethodParentCallRector::class, [
            ParentClassWithNewConstructor::class => '__construct',
        ]);
};
