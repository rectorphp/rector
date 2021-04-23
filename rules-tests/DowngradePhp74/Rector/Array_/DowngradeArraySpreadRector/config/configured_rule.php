<?php

declare(strict_types=1);

use Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeArraySpreadRector::class);
};
