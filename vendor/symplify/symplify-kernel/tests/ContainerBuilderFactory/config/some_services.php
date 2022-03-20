<?php

declare (strict_types=1);
namespace RectorPrefix20220320;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220320\Symplify\SmartFileSystem\SmartFileSystem;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\RectorPrefix20220320\Symplify\SmartFileSystem\SmartFileSystem::class);
};
