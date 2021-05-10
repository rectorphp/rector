<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector::class);
};
