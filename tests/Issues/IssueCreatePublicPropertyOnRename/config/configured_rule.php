<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenamePropertyToMatchTypeRector::class);
    $services->set(CompleteDynamicPropertiesRector::class);
};
