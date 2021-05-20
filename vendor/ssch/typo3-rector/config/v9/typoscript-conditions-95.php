<?php

declare (strict_types=1);
namespace RectorPrefix20210520;

use Ssch\TYPO3Rector\TypoScript\Conditions\ApplicationContextConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\BrowserConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\CompatVersionConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\GlobalStringConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\GlobalVarConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\HostnameConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\IPConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\LanguageConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\LoginUserConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\PageConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\PIDinRootlineConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\TimeConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\TreeLevelConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\UsergroupConditionMatcherMatcher;
use Ssch\TYPO3Rector\TypoScript\Conditions\VersionConditionMatcher;
use Ssch\TYPO3Rector\TypoScript\Visitors\OldConditionToExpressionLanguageVisitor;
use RectorPrefix20210520\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210520\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\ApplicationContextConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\BrowserConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\CompatVersionConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\GlobalStringConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\GlobalVarConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\HostnameConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\IPConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\LanguageConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\LoginUserConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\PageConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\PIDinRootlineConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\TimeConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\TreeLevelConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\UsergroupConditionMatcherMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Conditions\VersionConditionMatcher::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\Visitors\OldConditionToExpressionLanguageVisitor::class);
};
