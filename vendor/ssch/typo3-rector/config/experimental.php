<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Ssch\TYPO3Rector\Rector\Experimental\OptionalConstructorToHardRequirementRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector::class)->configure([new \Rector\Transform\ValueObject\MethodCallToStaticCall('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'get', 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance')]);
    $services->set(\Ssch\TYPO3Rector\Rector\Experimental\OptionalConstructorToHardRequirementRector::class);
};
