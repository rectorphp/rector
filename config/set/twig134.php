<?php

declare(strict_types=1);

use Rector\Symfony\Rector\Return_\SimpleFunctionAndFilterRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SimpleFunctionAndFilterRector::class);
};
