<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RefactorProcessOutputRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RefactorPropertiesOfTypoScriptFrontendControllerRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RemoveFlushCachesRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RemoveInitMethodFromPageRepositoryRector;
use Ssch\TYPO3Rector\Rector\v9\v5\RemoveInternalAnnotationRector;
use Ssch\TYPO3Rector\Rector\v9\v5\UsePackageManagerActivePackagesRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v5\UsePackageManagerActivePackagesRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Core\\Resource\\ResourceStorage', 'dumpFileContents', 'streamFile')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v5\RemoveFlushCachesRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v5\RemoveInternalAnnotationRector::class);
    $rectorConfig->ruleWithConfiguration(\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::class, [__DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/adminpanel/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/backend/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/core/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/extbase/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/fluid/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/info/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/lowlevel/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/recordlist/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/reports/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/t3editor/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/9.5/typo3/sysext/workspaces/Migrations/Code/ClassAliasMap.php']);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v5\RemoveInitMethodFromPageRepositoryRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v5\RefactorProcessOutputRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v5\RefactorPropertiesOfTypoScriptFrontendControllerRector::class);
};
