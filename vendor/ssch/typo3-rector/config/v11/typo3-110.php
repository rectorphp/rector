<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Ssch\TYPO3Rector\Rector\v11\v0\DateTimeAspectInsteadOfGlobalsExecTimeRector;
use Ssch\TYPO3Rector\Rector\v11\v0\ExtbaseControllerActionsMustReturnResponseInterfaceRector;
use Ssch\TYPO3Rector\Rector\v11\v0\ForwardResponseInsteadOfForwardMethodRector;
use Ssch\TYPO3Rector\Rector\v11\v0\GetClickMenuOnIconTagParametersRector;
use Ssch\TYPO3Rector\Rector\v11\v0\RemoveAddQueryStringMethodRector;
use Ssch\TYPO3Rector\Rector\v11\v0\RemoveLanguageModeMethodsFromTypo3QuerySettingsRector;
use Ssch\TYPO3Rector\Rector\v11\v0\ReplaceInjectAnnotationWithMethodRector;
use Ssch\TYPO3Rector\Rector\v11\v0\SubstituteConstantsModeAndRequestTypeRector;
use Ssch\TYPO3Rector\Rector\v11\v0\UniqueListFromStringUtilityRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v0\ForwardResponseInsteadOfForwardMethodRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v0\DateTimeAspectInsteadOfGlobalsExecTimeRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v0\UniqueListFromStringUtilityRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v0\GetClickMenuOnIconTagParametersRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v0\RemoveAddQueryStringMethodRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v0\ExtbaseControllerActionsMustReturnResponseInterfaceRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v0\SubstituteConstantsModeAndRequestTypeRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v0\RemoveLanguageModeMethodsFromTypo3QuerySettingsRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector::class, [new \Rector\Transform\ValueObject\StaticCallToFuncCall('TYPO3\\CMS\\Core\\Utility\\StringUtility', 'beginsWith', 'str_starts_with'), new \Rector\Transform\ValueObject\StaticCallToFuncCall('TYPO3\\CMS\\Core\\Utility\\StringUtility', 'endsWith', 'str_ends_with'), new \Rector\Transform\ValueObject\StaticCallToFuncCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'isFirstPartOfStr', 'str_starts_with')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v0\ReplaceInjectAnnotationWithMethodRector::class);
};
