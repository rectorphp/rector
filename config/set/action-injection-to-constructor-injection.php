<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector;
use Rector\DependencyInjection\Rector\Variable\ReplaceVariableByPropertyFetchRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(ActionInjectionToConstructorInjectionRector::class);
    $services->set(ReplaceVariableByPropertyFetchRector::class);
};
