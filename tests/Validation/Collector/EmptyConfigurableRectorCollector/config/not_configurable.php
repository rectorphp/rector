<?php

declare(strict_types=1);

use Rector\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ChangeLocalPropertyToVariableRector::class);
};
