<?php

declare(strict_types=1);

use Rector\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Source\MethodCaller;
use Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Source\StaticCaller;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveMethodCallParamRector::class)
        ->configure([
            new RemoveMethodCallParam(MethodCaller::class, 'process', 1),
            new RemoveMethodCallParam(StaticCaller::class, 'remove', 3),
        ]);
};
