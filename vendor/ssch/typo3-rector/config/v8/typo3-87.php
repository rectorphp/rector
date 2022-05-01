<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\BackendUtilityGetRecordRawRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\DataHandlerRmCommaRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\TemplateServiceSplitConfArrayRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\RefactorRemovedMarkerMethodsFromContentObjectRendererRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\ChangeAttemptsParameterConsoleOutputRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['TYPO3\\CMS\\Extbase\\Service\\TypoScriptService' => 'TYPO3\\CMS\\Core\\TypoScript\\TypoScriptService']);
    $rectorConfig->ruleWithConfiguration(\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::class, [__DIR__ . '/../../Migrations/TYPO3/8.7/typo3/sysext/extbase/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/8.7/typo3/sysext/fluid/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/8.7/typo3/sysext/version/Migrations/Code/ClassAliasMap.php']);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class, [new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'csvValues', 'TYPO3\\CMS\\Core\\Utility\\CsvUtility', 'csvValues')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\BackendUtilityGetRecordsByFieldToQueryBuilderRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\RefactorPrintContentMethodsRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\RefactorArrayBrowserWrapValueRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\DataHandlerVariousMethodsAndMethodArgumentsRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v7\UseCachingFrameworkInsteadGetAndStoreHashRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\FileProcessor\Fluid\Rector\DefaultSwitchFluidRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\LibFluidContentToLibContentElementRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\FileProcessor\TypoScript\PostRector\LibFluidContentToContentElementTypoScriptPostRector::class);
};
