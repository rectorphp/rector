<?php

declare(strict_types=1);

use Rector\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(HelperFuncCallToFacadeClassRector::class);
};
