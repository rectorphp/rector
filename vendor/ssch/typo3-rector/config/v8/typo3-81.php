<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v8\v1\Array2XmlCsToArray2XmlRector;
use Ssch\TYPO3Rector\Rector\v8\v1\GeneralUtilityToUpperAndLowerRector;
use Ssch\TYPO3Rector\Rector\v8\v1\RefactorDbConstantsRector;
use Ssch\TYPO3Rector\Rector\v8\v1\RefactorVariousGeneralUtilityMethodsRector;
use Ssch\TYPO3Rector\Rector\v8\v1\TypoScriptFrontendControllerCharsetConverterRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RefactorDbConstantsRector::class);
    $rectorConfig->rule(Array2XmlCsToArray2XmlRector::class);
    $rectorConfig->rule(TypoScriptFrontendControllerCharsetConverterRector::class);
    $rectorConfig->rule(GeneralUtilityToUpperAndLowerRector::class);
    $rectorConfig->rule(RefactorVariousGeneralUtilityMethodsRector::class);
};
