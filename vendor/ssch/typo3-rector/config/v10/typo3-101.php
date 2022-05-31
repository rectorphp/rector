<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Ssch\TYPO3Rector\Rector\v10\v1\BackendUtilityEditOnClickRector;
use Ssch\TYPO3Rector\Rector\v10\v1\RefactorInternalPropertiesOfTSFERector;
use Ssch\TYPO3Rector\Rector\v10\v1\RegisterPluginWithVendorNameRector;
use Ssch\TYPO3Rector\Rector\v10\v1\RemoveEnableMultiSelectFilterTextfieldRector;
use Ssch\TYPO3Rector\Rector\v10\v1\SendNotifyEmailToMailApiRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v1\RegisterPluginWithVendorNameRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v1\BackendUtilityEditOnClickRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::class, [new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'changeLog', 'getChangeLog', 'setChangelog', ['bla']), new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'lastHistoryEntry', 'getLastHistoryEntryNumber', null, [])]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'createChangeLog', 'getChangeLog'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'getElementData', 'getElementInformation'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'createMultipleDiff', 'getDiff'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'setLastHistoryEntry', 'setLastHistoryEntryNumber')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v1\SendNotifyEmailToMailApiRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v1\RefactorInternalPropertiesOfTSFERector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v1\RemoveEnableMultiSelectFilterTextfieldRector::class);
};
