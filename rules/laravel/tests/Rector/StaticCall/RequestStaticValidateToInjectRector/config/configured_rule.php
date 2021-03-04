<?php

declare(strict_types=1);

use Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RequestStaticValidateToInjectRector::class);
};
