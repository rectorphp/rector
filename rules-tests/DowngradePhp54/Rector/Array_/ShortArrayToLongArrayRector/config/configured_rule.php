<?php

declare(strict_types=1);

use Rector\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ShortArrayToLongArrayRector::class);
};
