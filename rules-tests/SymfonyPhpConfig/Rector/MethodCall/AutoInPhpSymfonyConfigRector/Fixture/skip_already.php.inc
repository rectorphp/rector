<?php

namespace Rector\Tests\SymfonyPhpConfig\Rector\MethodCall\AutoInPhpSymfonyConfigRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();
};
