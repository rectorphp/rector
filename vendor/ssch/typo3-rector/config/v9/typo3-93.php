<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

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
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RemoveColPosParameterRector::class);
    $rectorConfig->rule(ValidateAnnotationRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('TYPO3\\CMS\\Backend\\Controller\\Page\\LocalizationController', 'getUsedLanguagesInPageAndColumn', 'getUsedLanguagesInPage')]);
    $rectorConfig->rule(BackendUtilityGetModuleUrlRector::class);
    $rectorConfig->rule(PropertyUserTsToMethodGetTsConfigOfBackendUserAuthenticationRector::class);
    $rectorConfig->rule(UseMethodGetPageShortcutDirectlyFromSysPageRector::class);
    $rectorConfig->rule(CopyMethodGetPidForModTSconfigRector::class);
    $rectorConfig->rule(BackendUserAuthenticationSimplelogRector::class);
    $rectorConfig->rule(MoveLanguageFilesFromExtensionLangRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\Argument', 'getValidationResults', 'validate'), new MethodCallRename('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\Arguments', 'getValidationResults', 'validate')]);
    $rectorConfig->rule(RefactorTsConfigRelatedMethodsRector::class);
};
