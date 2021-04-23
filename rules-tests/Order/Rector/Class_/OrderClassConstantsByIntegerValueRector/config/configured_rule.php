<?php

declare(strict_types=1);

use Rector\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(OrderClassConstantsByIntegerValueRector::class);
};
