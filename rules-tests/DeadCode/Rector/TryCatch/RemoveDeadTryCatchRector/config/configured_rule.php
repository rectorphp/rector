<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveDeadTryCatchRector::class);
};
