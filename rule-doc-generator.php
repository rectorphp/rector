<?php

declare(strict_types=1);

use Rector\RuleDocGenerator\Category\RectorCategoryInferer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->defaults()
        ->autowire();

    $services->set(RectorCategoryInferer::class);
};
