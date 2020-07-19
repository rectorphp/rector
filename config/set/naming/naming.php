<?php

declare(strict_types=1);

use Rector\Naming\Rector\Assign\RenameVariableToMatchGetMethodNameRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenamePropertyToMatchTypeRector::class);

    // @todo add in separate PR, many changes now
    // $services->set(RenameVariableToMatchNewTypeRector::class);
//    $services->set(RenameVariableToMatchGetMethodNameRector::class);
};
