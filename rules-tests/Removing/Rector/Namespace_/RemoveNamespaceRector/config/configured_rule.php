<?php

declare(strict_types=1);

use Rector\Removing\Rector\Namespace_\RemoveNamespaceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveNamespaceRector::class)
        ->configure(['App']);
};
