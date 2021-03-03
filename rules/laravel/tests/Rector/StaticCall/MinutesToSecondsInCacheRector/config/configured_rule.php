<?php

declare(strict_types=1);

use Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MinutesToSecondsInCacheRector::class);
};
