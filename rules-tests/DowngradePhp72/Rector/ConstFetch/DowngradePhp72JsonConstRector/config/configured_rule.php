<?php

declare(strict_types=1);

use Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradePhp72JsonConstRector::class);
};
