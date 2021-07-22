<?php

declare(strict_types=1);

use Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeFinalizePublicClassConstantRector::class);
};
