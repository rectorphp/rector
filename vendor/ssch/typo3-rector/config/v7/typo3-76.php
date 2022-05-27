<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Ssch\TYPO3Rector\Rector\v7\v6\RenamePiListBrowserResultsRector;
use Ssch\TYPO3Rector\Rector\v7\v6\WrapClickMenuOnIconRector;
use RectorPrefix20220527\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RenamePiListBrowserResultsRector::class);
    $rectorConfig->ruleWithConfiguration(MethodCallToStaticCallRector::class, [new MethodCallToStaticCall('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'issueCommand', 'TYPO3\\CMS\\Backend\\Utility\\BackendUtility', 'getLinkToDataHandlerAction')]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_LEFT', LikeWildcard::class . '::WILDCARD_LEFT'), new RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_RIGHT', LikeWildcard::class . '::WILDCARD_RIGHT'), new RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_LEFT', LikeWildcard::class . '::WILDCARD_LEFT'), new RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_RIGHT', LikeWildcard::class . '::WILDCARD_RIGHT')]);
    $rectorConfig->rule(WrapClickMenuOnIconRector::class);
};
