<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\FileProcessor\Fluid\Rector\DefaultSwitchFluidRector;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\PostRector\LibFluidContentToContentElementTypoScriptPostRector;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\LibFluidContentToLibContentElementRector;
use Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use Ssch\TYPO3Rector\Rector\v8\v7\BackendUtilityGetRecordRawRector;
use Ssch\TYPO3Rector\Rector\v8\v7\BackendUtilityGetRecordsByFieldToQueryBuilderRector;
use Ssch\TYPO3Rector\Rector\v8\v7\ChangeAttemptsParameterConsoleOutputRector;
use Ssch\TYPO3Rector\Rector\v8\v7\DataHandlerRmCommaRector;
use Ssch\TYPO3Rector\Rector\v8\v7\DataHandlerVariousMethodsAndMethodArgumentsRector;
use Ssch\TYPO3Rector\Rector\v8\v7\RefactorArrayBrowserWrapValueRector;
use Ssch\TYPO3Rector\Rector\v8\v7\RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRector;
use Ssch\TYPO3Rector\Rector\v8\v7\RefactorPrintContentMethodsRector;
use Ssch\TYPO3Rector\Rector\v8\v7\RefactorRemovedMarkerMethodsFromContentObjectRendererRector;
use Ssch\TYPO3Rector\Rector\v8\v7\TemplateServiceSplitConfArrayRector;
use Ssch\TYPO3Rector\Rector\v8\v7\UseCachingFrameworkInsteadGetAndStoreHashRector;
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
