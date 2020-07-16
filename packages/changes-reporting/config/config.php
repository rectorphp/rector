<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->load('Rector\ChangesReporting\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Contract/*', __DIR__ . '/../src/ValueObject/*']);
};
