<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v1\Array2XmlCsToArray2XmlRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v1\GeneralUtilityToUpperAndLowerRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v1\RefactorDbConstantsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v1\RefactorVariousGeneralUtilityMethodsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v1\TypoScriptFrontendControllerCharsetConverterRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RefactorDbConstantsRector::class);
    $rectorConfig->rule(Array2XmlCsToArray2XmlRector::class);
    $rectorConfig->rule(TypoScriptFrontendControllerCharsetConverterRector::class);
    $rectorConfig->rule(GeneralUtilityToUpperAndLowerRector::class);
    $rectorConfig->rule(RefactorVariousGeneralUtilityMethodsRector::class);
};
