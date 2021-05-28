<?php

declare (strict_types=1);
namespace RectorPrefix20210528;

use Ssch\TYPO3Rector\Rector\v7\v1\GetTemporaryImageWithTextRector;
use RectorPrefix20210528\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210528\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v1\GetTemporaryImageWithTextRector::class);
};
