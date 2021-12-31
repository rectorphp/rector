<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallConnectDbRector;
use Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallLoadTcaRector;
use Ssch\TYPO3Rector\Rector\v7\v0\TypeHandlingServiceToTypeHandlingUtilityRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallConnectDbRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v0\RemoveMethodCallLoadTcaRector::class);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['TYPO3\\CMS\\Backend\\Template\\MediumDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'TYPO3\\CMS\\Backend\\Template\\SmallDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'TYPO3\\CMS\\Backend\\Template\\StandardDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'TYPO3\\CMS\\Backend\\Template\\BigDocumentTemplate' => 'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate']);
    $services->set(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class)->configure([new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'int_from_ver', 'TYPO3\\CMS\\Core\\Utility\\VersionNumberUtility', 'convertVersionNumberToInteger')]);
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v0\TypeHandlingServiceToTypeHandlingUtilityRector::class);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettingsInterface', 'setSysLanguageUid', 'setLanguageUid'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettingsInterface', 'getSysLanguageUid', 'getLanguageUid'), new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'create', 'get')]);
};
