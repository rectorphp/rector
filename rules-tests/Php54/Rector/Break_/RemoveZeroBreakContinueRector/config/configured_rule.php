<?php

declare(strict_types=1);

use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RemoveZeroBreakContinueRector::class);
};
