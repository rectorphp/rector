<?php

declare(strict_types=1);

use Rector\DowngradePhp71\Rector\FuncCall\DowngradeIsIterableRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeIsIterableRector::class);
};
