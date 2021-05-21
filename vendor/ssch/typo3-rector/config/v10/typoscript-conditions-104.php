<?php

declare (strict_types=1);
namespace RectorPrefix20210521;

use Ssch\TYPO3Rector\TypoScript\Conditions\PIDupinRootlineConditionMatcher;
use RectorPrefix20210521\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210521\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\PIDupinRootlineConditionMatcher::class);
};
