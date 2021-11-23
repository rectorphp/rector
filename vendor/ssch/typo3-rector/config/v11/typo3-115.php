<?php

declare (strict_types=1);
namespace RectorPrefix20211123;

use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\Rector\General\MethodGetInstanceToMakeInstanceCallRector;
use Ssch\TYPO3Rector\Rector\v11\v5\FlexFormToolsArrayValueByPathRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v5\FlexFormToolsArrayValueByPathRector::class);
    $services->set('rename_static_method_general_utility_is_abs_path_to_path_utility_is_absolute_path')->class(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'isAbsPath', 'TYPO3\\CMS\\Core\\Utility\\PathUtility', 'isAbsolutePath')])]]);
    $services->set('get_instance_call_to_make_instance_typo3_11_5')->class(\Ssch\TYPO3Rector\Rector\General\MethodGetInstanceToMakeInstanceCallRector::class)->call('configure', [[\Ssch\TYPO3Rector\Rector\General\MethodGetInstanceToMakeInstanceCallRector::CLASSES_GET_INSTANCE_TO_MAKE_INSTANCE => ['TYPO3\\CMS\\Core\\Resource\\Index\\ExtractorRegistry', 'TYPO3\\CMS\\Core\\Resource\\Index\\FileIndexRepository', 'TYPO3\\CMS\\Core\\Resource\\Index\\MetaDataRepository', 'TYPO3\\CMS\\Core\\Resource\\OnlineMedia\\Helpers\\OnlineMediaHelperRegistry', 'TYPO3\\CMS\\Core\\Resource\\Rendering\\RendererRegistry', 'TYPO3\\CMS\\Core\\Resource\\TextExtraction\\TextExtractorRegistry', 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'TYPO3\\CMS\\Form\\Service\\TranslationService', 'TYPO3\\CMS\\T3editor\\Registry\\AddonRegistry', 'TYPO3\\CMS\\T3editor\\Registry\\ModeRegistry']]]);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v5\RemoveDefaultInternalTypeDBRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v5\ReplaceTSFEATagParamsCallOnGlobalsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v5\HandleCObjRendererATagParamsMethodRector::class);
};
