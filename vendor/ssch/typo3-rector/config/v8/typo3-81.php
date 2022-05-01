<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v8\v1\Array2XmlCsToArray2XmlRector;
use Ssch\TYPO3Rector\Rector\v8\v1\GeneralUtilityToUpperAndLowerRector;
use Ssch\TYPO3Rector\Rector\v8\v1\RefactorDbConstantsRector;
use Ssch\TYPO3Rector\Rector\v8\v1\RefactorVariousGeneralUtilityMethodsRector;
use Ssch\TYPO3Rector\Rector\v8\v1\TypoScriptFrontendControllerCharsetConverterRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v1\RefactorDbConstantsRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v1\Array2XmlCsToArray2XmlRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v1\TypoScriptFrontendControllerCharsetConverterRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v1\GeneralUtilityToUpperAndLowerRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v1\RefactorVariousGeneralUtilityMethodsRector::class);
};
