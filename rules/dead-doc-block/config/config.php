<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();

    $services->load('Rector\DeadDocBlock\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Rector']);
};
