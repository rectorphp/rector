<?php

declare(strict_types=1);

use Rector\ValueObjects\Rector\Class_\PsalmImmutableToConstructorRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PsalmImmutableToConstructorRector::class);
};
