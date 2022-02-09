<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Ssch\TYPO3Rector\Rector\v10\v1\BackendUtilityEditOnClickRector;
use Ssch\TYPO3Rector\Rector\v10\v1\RefactorInternalPropertiesOfTSFERector;
use Ssch\TYPO3Rector\Rector\v10\v1\RegisterPluginWithVendorNameRector;
use Ssch\TYPO3Rector\Rector\v10\v1\SendNotifyEmailToMailApiRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v1\RegisterPluginWithVendorNameRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v1\BackendUtilityEditOnClickRector::class);
    $services->set(\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::class)->configure([new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'changeLog', 'getChangeLog', 'setChangelog', ['bla']), new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'lastHistoryEntry', 'getLastHistoryEntryNumber', null, [])]);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'createChangeLog', 'getChangeLog'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'getElementData', 'getElementInformation'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'createMultipleDiff', 'getDiff'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'setLastHistoryEntry', 'setLastHistoryEntryNumber')]);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v1\SendNotifyEmailToMailApiRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v1\RefactorInternalPropertiesOfTSFERector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v1\RemoveEnableMultiSelectFilterTextfieldRector::class);
};
