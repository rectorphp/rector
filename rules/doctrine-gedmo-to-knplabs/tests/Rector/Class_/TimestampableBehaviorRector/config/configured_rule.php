<?php

declare(strict_types=1);

use Rector\DoctrineGedmoToKnplabs\Rector\Class_\TimestampableBehaviorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(TimestampableBehaviorRector::class);
};
