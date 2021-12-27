<?php

declare(strict_types=1);

use Rector\Php74\Rector\Function_\ReservedFnFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReservedFnFunctionRector::class);
};
