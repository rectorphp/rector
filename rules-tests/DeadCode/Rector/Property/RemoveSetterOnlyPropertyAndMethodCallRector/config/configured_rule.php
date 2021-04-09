<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveSetterOnlyPropertyAndMethodCallRector::class);
};
