<?php

declare(strict_types=1);

use Rector\MockeryToProphecy\Rector\ClassMethod\MockeryCreateMockToProphizeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MockeryCreateMockToProphizeRector::class);
};
