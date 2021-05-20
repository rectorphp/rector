<?php

declare (strict_types=1);
namespace RectorPrefix20210520;

use Rector\NetteToSymfony\Rector\Class_\NetteTesterClassToPHPUnitClassRector;
use Rector\NetteToSymfony\Rector\StaticCall\NetteAssertToPHPUnitAssertRector;
use RectorPrefix20210520\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210520\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../../../../../config/config.php');
    $services = $containerConfigurator->services();
    $services->set(\Rector\NetteToSymfony\Rector\StaticCall\NetteAssertToPHPUnitAssertRector::class);
    $services->set(\Rector\NetteToSymfony\Rector\Class_\NetteTesterClassToPHPUnitClassRector::class);
};
