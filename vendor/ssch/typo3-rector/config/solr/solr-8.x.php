<?php

declare (strict_types=1);
namespace RectorPrefix20210529;

use RectorPrefix20210529\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210529\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $containerConfigurator->import(__DIR__ . '/v8/*');
};
