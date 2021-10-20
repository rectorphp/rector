<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Ssch\TYPO3Rector\Rector\v8\v1\Array2XmlCsToArray2XmlRector;
use Ssch\TYPO3Rector\Rector\v8\v1\GeneralUtilityToUpperAndLowerRector;
use Ssch\TYPO3Rector\Rector\v8\v1\RefactorDbConstantsRector;
use Ssch\TYPO3Rector\Rector\v8\v1\RefactorVariousGeneralUtilityMethodsRector;
use Ssch\TYPO3Rector\Rector\v8\v1\TypoScriptFrontendControllerCharsetConverterRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v1\RefactorDbConstantsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v1\Array2XmlCsToArray2XmlRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v1\TypoScriptFrontendControllerCharsetConverterRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v1\GeneralUtilityToUpperAndLowerRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v1\RefactorVariousGeneralUtilityMethodsRector::class);
};
