<?php

declare(strict_types=1);

use Rector\DeadDocBlock\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveUselessReturnTagRector::class);
};
