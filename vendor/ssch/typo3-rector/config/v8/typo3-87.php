<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20220606\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\RenameStaticMethod;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\Fluid\Rector\DefaultSwitchFluidRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\PostRector\v8\v7\LibFluidContentToContentElementTypoScriptPostRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\v8\v7\LibFluidContentToLibContentElementRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\BackendUtilityGetRecordRawRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\BackendUtilityGetRecordsByFieldToQueryBuilderRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\ChangeAttemptsParameterConsoleOutputRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\DataHandlerRmCommaRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\DataHandlerVariousMethodsAndMethodArgumentsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\RefactorArrayBrowserWrapValueRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\RefactorPrintContentMethodsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\RefactorRemovedMarkerMethodsFromContentObjectRendererRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\TemplateServiceSplitConfArrayRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7\UseCachingFrameworkInsteadGetAndStoreHashRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(BackendUtilityGetRecordRawRector::class);
    $rectorConfig->rule(DataHandlerRmCommaRector::class);
    $rectorConfig->rule(TemplateServiceSplitConfArrayRector::class);
    $rectorConfig->rule(RefactorRemovedMarkerMethodsFromContentObjectRendererRector::class);
    $rectorConfig->rule(ChangeAttemptsParameterConsoleOutputRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['TYPO3\\CMS\\Extbase\\Service\\TypoScriptService' => 'TYPO3\\CMS\\Core\\TypoScript\\TypoScriptService']);
    $rectorConfig->ruleWithConfiguration(RenameClassMapAliasRector::class, [__DIR__ . '/../../Migrations/TYPO3/8.7/typo3/sysext/extbase/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/8.7/typo3/sysext/fluid/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/8.7/typo3/sysext/version/Migrations/Code/ClassAliasMap.php']);
    $rectorConfig->ruleWithConfiguration(RenameStaticMethodRector::class, [new RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'csvValues', 'TYPO3\\CMS\\Core\\Utility\\CsvUtility', 'csvValues')]);
    $rectorConfig->rule(BackendUtilityGetRecordsByFieldToQueryBuilderRector::class);
    $rectorConfig->rule(RefactorPrintContentMethodsRector::class);
    $rectorConfig->rule(RefactorArrayBrowserWrapValueRector::class);
    $rectorConfig->rule(DataHandlerVariousMethodsAndMethodArgumentsRector::class);
    $rectorConfig->rule(RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRector::class);
    $rectorConfig->rule(UseCachingFrameworkInsteadGetAndStoreHashRector::class);
    $rectorConfig->rule(DefaultSwitchFluidRector::class);
    $rectorConfig->rule(LibFluidContentToLibContentElementRector::class);
    $rectorConfig->rule(LibFluidContentToContentElementTypoScriptPostRector::class);
};
