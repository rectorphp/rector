<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
    ]);

    $services = $containerConfigurator->services();
    $services->set(RemoveAlwaysElseRector::class);
    $services->set(RemoveUnusedPrivateMethodRector::class);
};

