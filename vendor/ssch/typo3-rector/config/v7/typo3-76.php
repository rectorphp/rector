<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\RenameClassConstFetch;
use RectorPrefix20220606\Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use RectorPrefix20220606\Rector\Transform\ValueObject\MethodCallToStaticCall;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v7\v6\RenamePiListBrowserResultsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v7\v6\WrapClickMenuOnIconRector;
use RectorPrefix20220606\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RenamePiListBrowserResultsRector::class);
    $rectorConfig->ruleWithConfiguration(MethodCallToStaticCallRector::class, [new MethodCallToStaticCall('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'issueCommand', 'TYPO3\\CMS\\Backend\\Utility\\BackendUtility', 'getLinkToDataHandlerAction')]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_LEFT', LikeWildcard::class . '::WILDCARD_LEFT'), new RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_RIGHT', LikeWildcard::class . '::WILDCARD_RIGHT'), new RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_LEFT', LikeWildcard::class . '::WILDCARD_LEFT'), new RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_RIGHT', LikeWildcard::class . '::WILDCARD_RIGHT')]);
    $rectorConfig->rule(WrapClickMenuOnIconRector::class);
};
