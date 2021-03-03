<?php

declare(strict_types=1);

use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DirNameFileConstantToDirConstantRector::class);
};
