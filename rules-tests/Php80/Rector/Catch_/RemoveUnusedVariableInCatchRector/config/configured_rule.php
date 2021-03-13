<?php

declare(strict_types=1);

use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveUnusedVariableInCatchRector::class);
};
