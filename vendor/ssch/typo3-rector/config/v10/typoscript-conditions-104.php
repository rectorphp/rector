<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\PIDupinRootlineConditionMatcher;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $services = $rectorConfig->services();
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\PIDupinRootlineConditionMatcher::class);
};
