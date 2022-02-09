<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Ssch\TYPO3Rector\Rector\v11\v0\DateTimeAspectInsteadOfGlobalsExecTimeRector;
use Ssch\TYPO3Rector\Rector\v11\v0\ExtbaseControllerActionsMustReturnResponseInterfaceRector;
use Ssch\TYPO3Rector\Rector\v11\v0\ForwardResponseInsteadOfForwardMethodRector;
use Ssch\TYPO3Rector\Rector\v11\v0\GetClickMenuOnIconTagParametersRector;
use Ssch\TYPO3Rector\Rector\v11\v0\RemoveAddQueryStringMethodRector;
use Ssch\TYPO3Rector\Rector\v11\v0\RemoveLanguageModeMethodsFromTypo3QuerySettingsRector;
use Ssch\TYPO3Rector\Rector\v11\v0\SubstituteConstantsModeAndRequestTypeRector;
use Ssch\TYPO3Rector\Rector\v11\v0\UniqueListFromStringUtilityRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v0\ForwardResponseInsteadOfForwardMethodRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v0\DateTimeAspectInsteadOfGlobalsExecTimeRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v0\UniqueListFromStringUtilityRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v0\GetClickMenuOnIconTagParametersRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v0\RemoveAddQueryStringMethodRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v0\ExtbaseControllerActionsMustReturnResponseInterfaceRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v0\SubstituteConstantsModeAndRequestTypeRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v0\RemoveLanguageModeMethodsFromTypo3QuerySettingsRector::class);
    $services->set(\Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector::class)->configure([new \Rector\Transform\ValueObject\StaticCallToFuncCall('TYPO3\\CMS\\Core\\Utility\\StringUtility', 'beginsWith', 'str_starts_with'), new \Rector\Transform\ValueObject\StaticCallToFuncCall('TYPO3\\CMS\\Core\\Utility\\StringUtility', 'endsWith', 'str_ends_with'), new \Rector\Transform\ValueObject\StaticCallToFuncCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'isFirstPartOfStr', 'str_starts_with')]);
    $services->set(\Ssch\TYPO3Rector\Rector\v11\v0\ReplaceInjectAnnotationWithMethodRector::class);
};
