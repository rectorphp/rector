<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\FileProcessor\Resources\Files\Rector\RenameExtTypoScriptFilesFileRector;
use Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use Ssch\TYPO3Rector\Rector\v12\v0\MigrateColsToSizeForTcaTypeNoneRector;
use Ssch\TYPO3Rector\Rector\v12\v0\MigrateInternalTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(MigrateColsToSizeForTcaTypeNoneRector::class);
    $rectorConfig->rule(MigrateInternalTypeRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassMapAliasRector::class, [__DIR__ . '/../../Migrations/TYPO3/12.0/typo3/sysext/backend/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/12.0/typo3/sysext/frontend/Migrations/Code/ClassAliasMap.php']);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v12\v0\ReplacePreviewUrlMethodRector::class);
    $rectorConfig->rule(RenameExtTypoScriptFilesFileRector::class);
};
