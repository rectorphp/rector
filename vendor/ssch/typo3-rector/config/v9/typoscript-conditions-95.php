<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\ApplicationContextConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\BrowserConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\CompatVersionConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\GlobalStringConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\GlobalVarConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\HostnameConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\IPConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\LanguageConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\LoginUserConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\PageConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\PIDinRootlineConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\TimeConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\TreeLevelConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\UsergroupConditionMatcherMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\VersionConditionMatcher;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\OldConditionToExpressionLanguageTypoScriptRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $services = $rectorConfig->services();
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\ApplicationContextConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\BrowserConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\CompatVersionConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\GlobalStringConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\GlobalVarConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\HostnameConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\IPConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\LanguageConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\LoginUserConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\PageConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\PIDinRootlineConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\TimeConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\TreeLevelConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\UsergroupConditionMatcherMatcher::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\VersionConditionMatcher::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\OldConditionToExpressionLanguageTypoScriptRector::class);
};
