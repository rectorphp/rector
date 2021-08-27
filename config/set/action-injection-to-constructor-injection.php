<?php

declare (strict_types=1);
namespace RectorPrefix20210827;

use Rector\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector;
use Rector\DependencyInjection\Rector\Variable\ReplaceVariableByPropertyFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector::class);
    $services->set(\Rector\DependencyInjection\Rector\Variable\ReplaceVariableByPropertyFetchRector::class);
};
