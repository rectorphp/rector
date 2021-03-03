<?php

declare(strict_types=1);

use Rector\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MakeOnlyUsedByChildrenProtectedRector::class);
};
