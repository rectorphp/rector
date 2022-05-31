<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v3\RemoveColPosParameterRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v3\ValidateAnnotationRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\Controller\\Page\\LocalizationController', 'getUsedLanguagesInPageAndColumn', 'getUsedLanguagesInPage')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v3\BackendUtilityGetModuleUrlRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v3\PropertyUserTsToMethodGetTsConfigOfBackendUserAuthenticationRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v3\UseMethodGetPageShortcutDirectlyFromSysPageRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v3\CopyMethodGetPidForModTSconfigRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v3\BackendUserAuthenticationSimplelogRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v3\MoveLanguageFilesFromExtensionLangRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\Argument', 'getValidationResults', 'validate'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\Arguments', 'getValidationResults', 'validate')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v3\RefactorTsConfigRelatedMethodsRector::class);
};
