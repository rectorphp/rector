<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v11\v4\AddSetConfigurationMethodToExceptionHandlerRector;
use Ssch\TYPO3Rector\Rector\v11\v4\ProvideCObjViaMethodRector;
use Ssch\TYPO3Rector\Rector\v11\v4\UseNativeFunctionInsteadOfGeneralUtilityShortMd5Rector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v4\UseNativeFunctionInsteadOfGeneralUtilityShortMd5Rector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v4\ProvideCObjViaMethodRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v4\AddSetConfigurationMethodToExceptionHandlerRector::class);
};
