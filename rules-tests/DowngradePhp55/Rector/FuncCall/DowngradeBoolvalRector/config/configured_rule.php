<?php

declare(strict_types=1);

use Rector\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeBoolvalRector::class);
};
