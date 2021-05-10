<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(UnwrapFutureCompatibleIfFunctionExistsRector::class);
};
