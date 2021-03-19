<?php

declare(strict_types=1);

use Rector\PHPOffice\Rector\StaticCall\CellStaticToCoordinateRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(CellStaticToCoordinateRector::class);
};
