<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\Function_\RemoveUnusedFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveUnusedFunctionRector::class);
};
