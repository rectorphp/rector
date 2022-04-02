<?php

declare(strict_types=1);

use Rector\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeThisInClosureRector::class);
};
