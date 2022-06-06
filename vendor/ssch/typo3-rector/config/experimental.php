<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use RectorPrefix20220606\Rector\Transform\ValueObject\MethodCallToStaticCall;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\Experimental\OptionalConstructorToHardRequirementRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(MethodCallToStaticCallRector::class, [new MethodCallToStaticCall('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'get', 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance')]);
    $rectorConfig->rule(OptionalConstructorToHardRequirementRector::class);
};
