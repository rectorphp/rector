<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\Rector\General\MethodGetInstanceToMakeInstanceCallRector;
use Ssch\TYPO3Rector\Rector\v11\v5\FlexFormToolsArrayValueByPathRector;
use Ssch\TYPO3Rector\Rector\v11\v5\HandleCObjRendererATagParamsMethodRector;
use Ssch\TYPO3Rector\Rector\v11\v5\RemoveDefaultInternalTypeDBRector;
use Ssch\TYPO3Rector\Rector\v11\v5\ReplaceTSFEATagParamsCallOnGlobalsRector;
use Ssch\TYPO3Rector\Rector\v11\v5\SubstituteBackendTemplateViewWithModuleTemplateRector;
use Ssch\TYPO3Rector\Rector\v11\v5\SubstituteGetIconFactoryAndGetPageRendererFromModuleTemplateRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(FlexFormToolsArrayValueByPathRector::class);
    $rectorConfig->ruleWithConfiguration(RenameStaticMethodRector::class, [new RenameStaticMethod('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'isAbsPath', 'TYPO3\\CMS\\Core\\Utility\\PathUtility', 'isAbsolutePath')]);
    $rectorConfig->ruleWithConfiguration(MethodGetInstanceToMakeInstanceCallRector::class, ['TYPO3\\CMS\\Core\\Resource\\Index\\ExtractorRegistry', 'TYPO3\\CMS\\Core\\Resource\\Index\\FileIndexRepository', 'TYPO3\\CMS\\Core\\Resource\\Index\\MetaDataRepository', 'TYPO3\\CMS\\Core\\Resource\\OnlineMedia\\Helpers\\OnlineMediaHelperRegistry', 'TYPO3\\CMS\\Core\\Resource\\Rendering\\RendererRegistry', 'TYPO3\\CMS\\Core\\Resource\\TextExtraction\\TextExtractorRegistry', 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'TYPO3\\CMS\\Form\\Service\\TranslationService', 'TYPO3\\CMS\\T3editor\\Registry\\AddonRegistry', 'TYPO3\\CMS\\T3editor\\Registry\\ModeRegistry']);
    $rectorConfig->rule(RemoveDefaultInternalTypeDBRector::class);
    $rectorConfig->rule(ReplaceTSFEATagParamsCallOnGlobalsRector::class);
    $rectorConfig->rule(HandleCObjRendererATagParamsMethodRector::class);
    $rectorConfig->rule(SubstituteBackendTemplateViewWithModuleTemplateRector::class);
    $rectorConfig->rule(SubstituteGetIconFactoryAndGetPageRendererFromModuleTemplateRector::class);
};
