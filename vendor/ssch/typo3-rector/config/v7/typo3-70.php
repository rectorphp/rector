<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallConnectDbRector;
use Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallLoadTcaRector;
use Ssch\TYPO3Rector\Rector\v7\v0\TypeHandlingServiceToTypeHandlingUtilityRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RemoveMethodCallConnectDbRector::class);
    $rectorConfig->rule(RemoveMethodCallLoadTcaRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['TYPO3\\CMS\\Backend\\Template\\MediumDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'TYPO3\\CMS\\Backend\\Template\\SmallDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'TYPO3\\CMS\\Backend\\Template\\StandardDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'TYPO3\\CMS\\Backend\\Template\\BigDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate']);
    $rectorConfig->ruleWithConfiguration(RenameStaticMethodRector::class, [new RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'int_from_ver', 'TYPO3\\CMS\\Core\\Utility\\VersionNumberUtility', 'convertVersionNumberToInteger')]);
    $rectorConfig->rule(TypeHandlingServiceToTypeHandlingUtilityRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettingsInterface', 'setSysLanguageUid', 'setLanguageUid'), new MethodCallRename('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettingsInterface', 'getSysLanguageUid', 'getLanguageUid'), new MethodCallRename('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'create', 'get')]);
};
