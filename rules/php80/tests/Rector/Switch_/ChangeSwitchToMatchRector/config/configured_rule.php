<?php

declare(strict_types=1);

use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeSwitchToMatchRector::class);
};
