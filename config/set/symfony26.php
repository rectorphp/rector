<?php

declare(strict_types=1);

use Rector\Symfony\Rector\MethodCall\AddFlashRector;
use Rector\Symfony\Rector\MethodCall\RedirectToRouteRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RedirectToRouteRector::class);

    $services->set(AddFlashRector::class);
};
