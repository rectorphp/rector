<?php

declare(strict_types=1);

use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReadOnlyPropertyRector::class);
};
