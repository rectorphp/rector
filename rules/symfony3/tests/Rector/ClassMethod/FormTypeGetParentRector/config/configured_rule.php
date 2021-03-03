<?php

declare(strict_types=1);

use Rector\Symfony3\Rector\ClassMethod\FormTypeGetParentRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FormTypeGetParentRector::class);
};
