<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SimplifyInArrayValuesRector::class);
};
