<?php

declare (strict_types=1);
namespace RectorPrefix20220304;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220304\Symplify\SmartFileSystem\SmartFileSystem;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\RectorPrefix20220304\Symplify\SmartFileSystem\SmartFileSystem::class);
};
