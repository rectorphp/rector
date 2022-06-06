<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3\BackendUserAuthenticationSimplelogRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3\BackendUtilityGetModuleUrlRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3\CopyMethodGetPidForModTSconfigRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3\MoveLanguageFilesFromExtensionLangRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3\PropertyUserTsToMethodGetTsConfigOfBackendUserAuthenticationRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3\RefactorTsConfigRelatedMethodsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3\RemoveColPosParameterRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3\UseMethodGetPageShortcutDirectlyFromSysPageRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3\ValidateAnnotationRector;
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
