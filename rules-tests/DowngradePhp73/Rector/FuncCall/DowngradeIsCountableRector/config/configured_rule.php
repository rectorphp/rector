<?php

declare(strict_types=1);

use Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeIsCountableRector::class);
};
