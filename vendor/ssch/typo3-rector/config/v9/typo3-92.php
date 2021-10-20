<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\Name\RenameClassRector;
use Ssch\TYPO3Rector\Rector\v9\v2\GeneralUtilityGetUrlRequestHeadersRector;
use Ssch\TYPO3Rector\Rector\v9\v2\PageNotFoundAndErrorHandlingRector;
use Ssch\TYPO3Rector\Rector\v9\v2\RenameMethodCallToEnvironmentMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v2\RenameMethodCallToEnvironmentMethodCallRector::class);
    $services->set('string_frontend_to_variable_frontend')->class(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => ['TYPO3\\CMS\\Core\\Cache\\Frontend\\StringFrontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend']]]);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v2\GeneralUtilityGetUrlRequestHeadersRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v2\PageNotFoundAndErrorHandlingRector::class);
};
