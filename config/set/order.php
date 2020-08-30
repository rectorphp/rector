<?php

declare(strict_types=1);

use Rector\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector;
use Rector\Order\Rector\Class_\OrderConstantsByVisibilityRector;
use Rector\Order\Rector\Class_\OrderMethodsByVisibilityRector;
use Rector\Order\Rector\Class_\OrderPrivateMethodsByUseRector;
use Rector\Order\Rector\Class_\OrderPropertiesByVisibilityRector;
use Rector\Order\Rector\Class_\OrderPropertyByComplexityRector;
use Rector\Order\Rector\Class_\OrderPublicInterfaceMethodRector;
use Rector\Order\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(OrderPrivateMethodsByUseRector::class);

    $services->set(OrderPublicInterfaceMethodRector::class);

    $services->set(OrderPropertyByComplexityRector::class);

    $services->set(OrderClassConstantsByIntegerValueRector::class);

    $services->set(OrderConstructorDependenciesByTypeAlphabeticallyRector::class);

    $services->set(OrderMethodsByVisibilityRector::class);

    $services->set(OrderPropertiesByVisibilityRector::class);

    $services->set(OrderConstantsByVisibilityRector::class);
};
