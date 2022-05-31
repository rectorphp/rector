<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Ssch\TYPO3Rector\Rector\Experimental\OptionalConstructorToHardRequirementRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector::class, [new \Rector\Transform\ValueObject\MethodCallToStaticCall('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'get', 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\Experimental\OptionalConstructorToHardRequirementRector::class);
};
