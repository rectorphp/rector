<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\FileProcessor\Fluid\Rector\DefaultSwitchFluidRector;
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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\BackendUtilityGetRecordRawRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\DataHandlerRmCommaRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\TemplateServiceSplitConfArrayRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\RefactorRemovedMarkerMethodsFromContentObjectRendererRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\ChangeAttemptsParameterConsoleOutputRector::class);
    $services->set('extbase_typoscriptservice_to_core_typoscriptservice')->class(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => ['TYPO3\\CMS\\Extbase\\Service\\TypoScriptService' => 'TYPO3\\CMS\\Core\\TypoScript\\TypoScriptService']]]);
    $services->set('rename_class_alias_maps_version_87')->class(\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::class)->call('configure', [[\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::CLASS_ALIAS_MAPS => [__DIR__ . '/../../Migrations/TYPO3/8.7/typo3/sysext/extbase/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/8.7/typo3/sysext/fluid/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/8.7/typo3/sysext/version/Migrations/Code/ClassAliasMap.php']]]);
    $services->set('general_utility_csv_values_to_csv_utility')->class(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'csvValues', 'TYPO3\\CMS\\Core\\Utility\\CsvUtility', 'csvValues')])]]);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\BackendUtilityGetRecordsByFieldToQueryBuilderRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\RefactorPrintContentMethodsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\RefactorArrayBrowserWrapValueRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\DataHandlerVariousMethodsAndMethodArgumentsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v7\UseCachingFrameworkInsteadGetAndStoreHashRector::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\Fluid\Rector\DefaultSwitchFluidRector::class);
};
