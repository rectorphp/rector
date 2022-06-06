<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v3\SubstituteExtbaseRequestGetBaseUriRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v3\SubstituteMethodRmFromListOfGeneralUtilityRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v3\SwitchBehaviorOfArrayUtilityMethodsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v3\UseLanguageTypeForLanguageFieldColumnRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(SubstituteMethodRmFromListOfGeneralUtilityRector::class);
    $rectorConfig->rule(SwitchBehaviorOfArrayUtilityMethodsRector::class);
    $rectorConfig->rule(UseLanguageTypeForLanguageFieldColumnRector::class);
    $rectorConfig->rule(SubstituteExtbaseRequestGetBaseUriRector::class);
};
