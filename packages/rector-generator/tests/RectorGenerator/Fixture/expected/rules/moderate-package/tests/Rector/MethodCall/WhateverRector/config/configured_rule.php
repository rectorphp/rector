<?php

declare(strict_types=1);

use Rector\ModeratePackage\Rector\MethodCall\WhateverRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(WhateverRector::class);
};
