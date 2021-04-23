<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RemoveAndTrueRector::class);
};
