<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Ssch\TYPO3Rector\Rector\v7\v4\InstantiatePageRendererExplicitlyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v4\InstantiatePageRendererExplicitlyRector::class);
};
