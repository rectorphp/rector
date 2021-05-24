<?php

declare (strict_types=1);
namespace RectorPrefix20210524;

use Ssch\TYPO3Rector\Rector\v7\v4\InstantiatePageRendererExplicitlyRector;
use RectorPrefix20210524\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210524\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v4\InstantiatePageRendererExplicitlyRector::class);
};
