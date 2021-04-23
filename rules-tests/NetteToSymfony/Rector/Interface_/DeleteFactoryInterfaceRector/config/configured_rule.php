<?php

declare(strict_types=1);

use Rector\NetteToSymfony\Rector\Interface_\DeleteFactoryInterfaceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DeleteFactoryInterfaceRector::class);
};
