<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

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
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $services = $rectorConfig->services();
    $services->set(ApplicationContextConditionMatcher::class);
    $services->set(BrowserConditionMatcher::class);
    $services->set(CompatVersionConditionMatcher::class);
    $services->set(GlobalStringConditionMatcher::class);
    $services->set(GlobalVarConditionMatcher::class);
    $services->set(HostnameConditionMatcher::class);
    $services->set(IPConditionMatcher::class);
    $services->set(LanguageConditionMatcher::class);
    $services->set(LoginUserConditionMatcher::class);
    $services->set(PageConditionMatcher::class);
    $services->set(PIDinRootlineConditionMatcher::class);
    $services->set(TimeConditionMatcher::class);
    $services->set(TreeLevelConditionMatcher::class);
    $services->set(UsergroupConditionMatcherMatcher::class);
    $services->set(VersionConditionMatcher::class);
    $rectorConfig->rule(OldConditionToExpressionLanguageTypoScriptRector::class);
};
