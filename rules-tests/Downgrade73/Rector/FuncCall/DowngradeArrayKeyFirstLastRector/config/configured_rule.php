<?php

declare(strict_types=1);

use Rector\Downgrade73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeArrayKeyFirstLastRector::class);
};
