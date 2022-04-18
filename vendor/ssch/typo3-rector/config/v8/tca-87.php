<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v3\RemovedTcaSelectTreeOptionsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v3\SoftReferencesFunctionalityRemovedRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v4\SubstituteOldWizardIconsRector::class)->configure(['add.gif' => 'actions-add', 'link_popup.gif' => 'actions-wizard-link', 'wizard_rte2.gif' => 'actions-wizard-rte', 'wizard_link.gif' => 'actions-wizard-rte', 'wizard_table.gif' => 'content-table', 'edit2.gif' => 'actions-open', 'list.gif' => 'actions-system-list-open', 'wizard_forms.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_forms.gif', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif' => 'actions-add', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_table.gif' => 'content-table', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif' => 'actions-open', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_list.gif' => 'actions-system-list-open', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif' => 'actions-wizard-link', 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif' => 'actions-wizard-rte']);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v4\RemoveOptionShowIfRteRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v5\RemoveOptionVersioningFollowPagesRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v5\MoveLanguageFilesFromLocallangToResourcesRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v5\RemoveSupportForTransForeignTableRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v6\MoveRequestUpdateOptionFromControlToColumnsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v6\RichtextFromDefaultExtrasToEnableRichtextRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v6\RefactorTCARector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v6\MigrateSelectShowIconTableRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v6\RemoveL10nModeNoCopyRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v6\MigrateOptionsOfTypeGroupRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\RemoveConfigMaxFromInputDateTimeFieldsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\RemoveLocalizationModeKeepIfNeededRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\MoveForeignTypesToOverrideChildTcaRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v6\MigrateLastPiecesOfDefaultExtrasRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v6\MoveTypeGroupSuggestWizardToSuggestOptionsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v6\MigrateSpecialConfigurationAndRemoveShowItemStylePointerConfigRector::class);
};
