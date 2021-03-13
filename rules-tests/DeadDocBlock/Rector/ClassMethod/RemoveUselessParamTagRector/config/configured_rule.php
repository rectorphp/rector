<?php

declare(strict_types=1);

use Rector\DeadDocBlock\Rector\ClassMethod\RemoveUselessParamTagRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveUselessParamTagRector::class);
};
