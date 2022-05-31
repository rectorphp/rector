<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallConnectDbRector;
use Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallLoadTcaRector;
use Ssch\TYPO3Rector\Rector\v7\v0\TypeHandlingServiceToTypeHandlingUtilityRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallConnectDbRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallLoadTcaRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['TYPO3\\CMS\\Backend\\Template\\MediumDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'TYPO3\\CMS\\Backend\\Template\\SmallDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'TYPO3\\CMS\\Backend\\Template\\StandardDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'TYPO3\\CMS\\Backend\\Template\\BigDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate']);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class, [new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'int_from_ver', 'TYPO3\\CMS\\Core\\Utility\\VersionNumberUtility', 'convertVersionNumberToInteger')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v7\v0\TypeHandlingServiceToTypeHandlingUtilityRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettingsInterface', 'setSysLanguageUid', 'setLanguageUid'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettingsInterface', 'getSysLanguageUid', 'getLanguageUid'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'create', 'get')]);
};
