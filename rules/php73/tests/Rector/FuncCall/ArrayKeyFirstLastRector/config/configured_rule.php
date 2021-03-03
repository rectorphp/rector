<?php

declare(strict_types=1);

use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayKeyFirstLastRector::class);
};
