<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeStrContainsRector::class);
};
