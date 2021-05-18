<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

use Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector;
use RectorPrefix20210518\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210518\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector::class);
};
