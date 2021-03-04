<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeArrayPushToArrayAssignRector::class);
};
