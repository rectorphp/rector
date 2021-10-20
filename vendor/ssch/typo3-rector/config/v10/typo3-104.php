<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector;
use Ssch\TYPO3Rector\Rector\v10\v4\SubstituteGeneralUtilityMethodsWithNativePhpFunctionsRector;
use Ssch\TYPO3Rector\Rector\v10\v4\UnifiedFileNameValidatorRector;
use Ssch\TYPO3Rector\Rector\v10\v4\UseFileGetContentsForGetUrlRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v4\UnifiedFileNameValidatorRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v4\SubstituteGeneralUtilityMethodsWithNativePhpFunctionsRector::class);
    $services->set('rename_static_method_is_running_on_cgi_server_api_to_is_running_on_cgi_server')->class(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'isRunningOnCgiServerApi', 'TYPO3\\CMS\\Core\\Core\\Environment', 'isRunningOnCgiServer')])]]);
    $services->set('rename_class_alias_maps_version_104')->class(\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::class)->call('configure', [[\Ssch\TYPO3Rector\Rector\Migrations\RenameClassMapAliasRector::CLASS_ALIAS_MAPS => [__DIR__ . '/../../Migrations/TYPO3/10.4/typo3/sysext/backend/Migrations/Code/ClassAliasMap.php', __DIR__ . '/../../Migrations/TYPO3/10.4/typo3/sysext/core/Migrations/Code/ClassAliasMap.php']]]);
    $services->set(\Ssch\TYPO3Rector\Rector\v10\v4\UseFileGetContentsForGetUrlRector::class);
};
