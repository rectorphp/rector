<?php

declare(strict_types=1);

use Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeArgumentUnpackingRector::class);
};
