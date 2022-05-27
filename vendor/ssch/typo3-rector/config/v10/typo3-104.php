<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use Ssch\TYPO3Rector\Rector\v10\v4\SubstituteGeneralUtilityMethodsWithNativePhpFunctionsRector;
use Ssch\TYPO3Rector\Rector\v10\v4\UnifiedFileNameValidatorRector;
use Ssch\TYPO3Rector\Rector\v10\v4\UseFileGetContentsForGetUrlRector;
use Ssch\TYPO3Rector\Rector\v10\v4\UseIconsFromSubFolderInIconRegistryRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(UnifiedFileNameValidatorRector::class);
    $rectorConfig->rule(SubstituteGeneralUtilityMethodsWithNativePhpFunctionsRector::class);
    $rectorConfig->ruleWithConfiguration(RenameStaticMethodRector::class, [new RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'isRunningOnCgiServerApi', 'TYPO3\\CMS\\Core\\Core\\Environment', 'isRunningOnCgiServer')]);
    $rectorConfig->ruleWithConfiguration(RenameClassMapAliasRector::class, [__DIR__ . '/../../Migrations/TYPO3/10.4/typo3/sysext/backend/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/10.4/typo3/sysext/core/Migrations/Code/ClassAliasMap.php']);
    $rectorConfig->rule(UseFileGetContentsForGetUrlRector::class);
    $rectorConfig->rule(UseIconsFromSubFolderInIconRegistryRector::class);
};
