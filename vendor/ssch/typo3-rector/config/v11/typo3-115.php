<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\RenameStaticMethod;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\General\MethodGetInstanceToMakeInstanceCallRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v5\FlexFormToolsArrayValueByPathRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v5\HandleCObjRendererATagParamsMethodRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v5\RemoveDefaultInternalTypeDBRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v5\ReplaceTSFEATagParamsCallOnGlobalsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v5\SubstituteBackendTemplateViewWithModuleTemplateRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v5\SubstituteGetIconFactoryAndGetPageRendererFromModuleTemplateRector;
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
