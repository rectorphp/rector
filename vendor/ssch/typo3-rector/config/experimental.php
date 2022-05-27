<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Ssch\TYPO3Rector\Rector\Experimental\OptionalConstructorToHardRequirementRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(MethodCallToStaticCallRector::class, [new MethodCallToStaticCall('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'get', 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance')]);
    $rectorConfig->rule(OptionalConstructorToHardRequirementRector::class);
};
