<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector;
use Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(SetClassWithArgumentToSetFactoryRector::class);
    $services->set(BuilderExpandToHelperExpandRector::class);
};
