<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Ssch\TYPO3Rector\Rector\v9\v3\BackendUserAuthenticationSimplelogRector;
use Ssch\TYPO3Rector\Rector\v9\v3\BackendUtilityGetModuleUrlRector;
use Ssch\TYPO3Rector\Rector\v9\v3\CopyMethodGetPidForModTSconfigRector;
use Ssch\TYPO3Rector\Rector\v9\v3\MoveLanguageFilesFromExtensionLangRector;
use Ssch\TYPO3Rector\Rector\v9\v3\PropertyUserTsToMethodGetTsConfigOfBackendUserAuthenticationRector;
use Ssch\TYPO3Rector\Rector\v9\v3\RefactorTsConfigRelatedMethodsRector;
use Ssch\TYPO3Rector\Rector\v9\v3\RemoveColPosParameterRector;
use Ssch\TYPO3Rector\Rector\v9\v3\UseMethodGetPageShortcutDirectlyFromSysPageRector;
use Ssch\TYPO3Rector\Rector\v9\v3\ValidateAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v3\RemoveColPosParameterRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v3\ValidateAnnotationRector::class);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\Controller\\Page\\LocalizationController', 'getUsedLanguagesInPageAndColumn', 'getUsedLanguagesInPage')]);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v3\BackendUtilityGetModuleUrlRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v3\PropertyUserTsToMethodGetTsConfigOfBackendUserAuthenticationRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v3\UseMethodGetPageShortcutDirectlyFromSysPageRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v3\CopyMethodGetPidForModTSconfigRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v3\BackendUserAuthenticationSimplelogRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v3\MoveLanguageFilesFromExtensionLangRector::class);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\Argument', 'getValidationResults', 'validate'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\Arguments', 'getValidationResults', 'validate')]);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v3\RefactorTsConfigRelatedMethodsRector::class);
};
