<?php

declare(strict_types=1);

use Rector\DoctrineGedmoToKnplabs\Rector\Class_\SoftDeletableBehaviorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SoftDeletableBehaviorRector::class);
};
