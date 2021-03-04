<?php

declare(strict_types=1);

use Rector\Symfony4\Rector\StaticCall\ProcessBuilderInstanceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ProcessBuilderInstanceRector::class);
};
