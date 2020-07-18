<?php

declare(strict_types=1);

use Rector\SymfonyPHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SelfContainerGetMethodCallFromTestToSetUpMethodRector::class);
};
