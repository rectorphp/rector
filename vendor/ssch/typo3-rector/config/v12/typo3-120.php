<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\Resources\Files\Rector\RenameExtTypoScriptFilesFileRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\MigrateColsToSizeForTcaTypeNoneRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\MigrateInternalTypeRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\typo3\RemoveUpdateRootlineDataRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\typo3\ReplaceContentObjectRendererGetMailToWithEmailLinkBuilderRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(MigrateColsToSizeForTcaTypeNoneRector::class);
    $rectorConfig->rule(MigrateInternalTypeRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassMapAliasRector::class, [__DIR__ . '/../../Migrations/TYPO3/12.0/typo3/sysext/backend/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/12.0/typo3/sysext/frontend/Migrations/Code/ClassAliasMap.php']);
    $rectorConfig->rule(\RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\ReplacePreviewUrlMethodRector::class);
    $rectorConfig->rule(RenameExtTypoScriptFilesFileRector::class);
    $rectorConfig->rule(\RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\typo3\ReplaceTSFECheckEnableFieldsRector::class);
    $rectorConfig->rule(ReplaceContentObjectRendererGetMailToWithEmailLinkBuilderRector::class);
    $rectorConfig->rule(RemoveUpdateRootlineDataRector::class);
    $rectorConfig->rule(\RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\typo3\ReplaceTSFEWithContextMethodsRector::class);
};
