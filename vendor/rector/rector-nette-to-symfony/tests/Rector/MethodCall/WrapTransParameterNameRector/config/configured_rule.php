<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../../../../../config/config.php');
    $services = $containerConfigurator->services();
    $services->set(WrapTransParameterNameRector::class);
};
