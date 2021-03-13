<?php

declare(strict_types=1);

use Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArraySpreadInsteadOfArrayMergeRector::class);
};
