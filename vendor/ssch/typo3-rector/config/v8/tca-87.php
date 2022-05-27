<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v8\v3\RemovedTcaSelectTreeOptionsRector;
use Ssch\TYPO3Rector\Rector\v8\v3\SoftReferencesFunctionalityRemovedRector;
use Ssch\TYPO3Rector\Rector\v8\v4\RemoveOptionShowIfRteRector;
use Ssch\TYPO3Rector\Rector\v8\v4\SubstituteOldWizardIconsRector;
use Ssch\TYPO3Rector\Rector\v8\v5\MoveLanguageFilesFromLocallangToResourcesRector;
use Ssch\TYPO3Rector\Rector\v8\v5\RemoveOptionVersioningFollowPagesRector;
use Ssch\TYPO3Rector\Rector\v8\v5\RemoveSupportForTransForeignTableRector;
use Ssch\TYPO3Rector\Rector\v8\v6\MigrateLastPiecesOfDefaultExtrasRector;
use Ssch\TYPO3Rector\Rector\v8\v6\MigrateOptionsOfTypeGroupRector;
use Ssch\TYPO3Rector\Rector\v8\v6\MigrateSelectShowIconTableRector;
use Ssch\TYPO3Rector\Rector\v8\v6\MigrateSpecialConfigurationAndRemoveShowItemStylePointerConfigRector;
use Ssch\TYPO3Rector\Rector\v8\v6\MoveRequestUpdateOptionFromControlToColumnsRector;
use Ssch\TYPO3Rector\Rector\v8\v6\MoveTypeGroupSuggestWizardToSuggestOptionsRector;
use Ssch\TYPO3Rector\Rector\v8\v6\RefactorTCARector;
use Ssch\TYPO3Rector\Rector\v8\v6\RemoveL10nModeNoCopyRector;
use Ssch\TYPO3Rector\Rector\v8\v6\RichtextFromDefaultExtrasToEnableRichtextRector;
use Ssch\TYPO3Rector\Rector\v8\v7\MoveForeignTypesToOverrideChildTcaRector;
use Ssch\TYPO3Rector\Rector\v8\v7\RemoveConfigMaxFromInputDateTimeFieldsRector;
use Ssch\TYPO3Rector\Rector\v8\v7\RemoveLocalizationModeKeepIfNeededRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RemovedTcaSelectTreeOptionsRector::class);
    $rectorConfig->rule(SoftReferencesFunctionalityRemovedRector::class);
    $rectorConfig->ruleWithConfiguration(SubstituteOldWizardIconsRector::class, ['add.gif' => 'actions-add', 'link_popup.gif' => 'actions-wizard-link', 'wizard_rte2.gif' => 'actions-wizard-rte', 'wizard_link.gif' => 'actions-wizard-rte', 'wizard_table.gif' => 'content-table', 'edit2.gif' => 'actions-open', 'list.gif' => 'actions-system-list-open', 'wizard_forms.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_forms.gif', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif' => 'actions-add', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_table.gif' => 'content-table', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif' => 'actions-open', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_list.gif' => 'actions-system-list-open', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif' => 'actions-wizard-link', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif' => 'actions-wizard-rte']);
    $rectorConfig->rule(RemoveOptionShowIfRteRector::class);
    $rectorConfig->rule(RemoveOptionVersioningFollowPagesRector::class);
    $rectorConfig->rule(MoveLanguageFilesFromLocallangToResourcesRector::class);
    $rectorConfig->rule(RemoveSupportForTransForeignTableRector::class);
    $rectorConfig->rule(MoveRequestUpdateOptionFromControlToColumnsRector::class);
    $rectorConfig->rule(RichtextFromDefaultExtrasToEnableRichtextRector::class);
    $rectorConfig->rule(RefactorTCARector::class);
    $rectorConfig->rule(MigrateSelectShowIconTableRector::class);
    $rectorConfig->rule(RemoveL10nModeNoCopyRector::class);
    $rectorConfig->rule(MigrateOptionsOfTypeGroupRector::class);
    $rectorConfig->rule(RemoveConfigMaxFromInputDateTimeFieldsRector::class);
    $rectorConfig->rule(RemoveLocalizationModeKeepIfNeededRector::class);
    $rectorConfig->rule(MoveForeignTypesToOverrideChildTcaRector::class);
    $rectorConfig->rule(MigrateLastPiecesOfDefaultExtrasRector::class);
    $rectorConfig->rule(MoveTypeGroupSuggestWizardToSuggestOptionsRector::class);
    $rectorConfig->rule(MigrateSpecialConfigurationAndRemoveShowItemStylePointerConfigRector::class);
};
