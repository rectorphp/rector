<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RefactorProcessOutputRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RefactorPropertiesOfTypoScriptFrontendControllerRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RemoveFlushCachesRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RemoveInitMethodFromPageRepositoryRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RemoveInternalAnnotationRector;
use Ssch\TYPO3Rector\Rector\v9\v5\UsePackageManagerActivePackagesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v5\UsePackageManagerActivePackagesRector::class);
    $services->set('resource_storage_dump_file_contents_to_stream_file')->class(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\MethodCall\RenameMethodRector::METHOD_CALL_RENAMES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', 'dumpFileContents', 'streamFile')])]]);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v5\RemoveFlushCachesRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v5\RemoveInternalAnnotationRector::class);
    $services->set('rename_class_alias_maps_version_95')->class(\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::class)->call('configure', [[\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::CLASS_ALIAS_MAPS => [__DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/adminpanel/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/backend/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/core/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/extbase/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/fluid/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/info/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/lowlevel/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/recordlist/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/reports/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/t3editor/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/workspaces/Migrations/Code/ClassAliasMap.php']]]);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v5\RemoveInitMethodFromPageRepositoryRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v5\RefactorProcessOutputRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v9\v5\RefactorPropertiesOfTypoScriptFrontendControllerRector::class);
};
