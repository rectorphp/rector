<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Ssch\TYPO3Rector\Rector\v10\v1\BackendUtilityEditOnClickRector;
use Ssch\TYPO3Rector\Rector\v10\v1\RefactorInternalPropertiesOfTSFERector;
use Ssch\TYPO3Rector\Rector\v10\v1\RegisterPluginWithVendorNameRector;
use Ssch\TYPO3Rector\Rector\v10\v1\SendNotifyEmailToMailApiRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v1\RegisterPluginWithVendorNameRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v1\BackendUtilityEditOnClickRector::class);
    $services->set('record_history_property_fetch_changelog_to_method_call_get_changelog')->class(\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::class)->call('configure', [[\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::PROPERTIES_TO_METHOD_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'changeLog', 'getChangeLog', 'setChangelog', ['bla']), new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'lastHistoryEntry', 'getLastHistoryEntryNumber', null, [])])]]);
    $services->set('record_history_rename_methods')->class(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\MethodCall\RenameMethodRector::METHOD_CALL_RENAMES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'createChangeLog', 'getChangeLog'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'getElementData', 'getElementInformation'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'createMultipleDiff', 'getDiff'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\History\\RecordHistory', 'setLastHistoryEntry', 'setLastHistoryEntryNumber')])]]);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v1\SendNotifyEmailToMailApiRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v1\RefactorInternalPropertiesOfTSFERector::class);
};
