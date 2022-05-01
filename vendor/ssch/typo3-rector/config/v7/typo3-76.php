<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Ssch\TYPO3Rector\Rector\v7\v6\RenamePiListBrowserResultsRector;
use Ssch\TYPO3Rector\Rector\v7\v6\WrapClickMenuOnIconRector;
use RectorPrefix20220501\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v7\v6\RenamePiListBrowserResultsRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector::class, [new \Rector\Transform\ValueObject\MethodCallToStaticCall('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'issueCommand', 'TYPO3\\CMS\\Backend\\Utility\\BackendUtility', 'getLinkToDataHandlerAction')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::class, [new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_LEFT', \RectorPrefix20220501\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_LEFT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_RIGHT', \RectorPrefix20220501\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_RIGHT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_LEFT', \RectorPrefix20220501\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_LEFT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_RIGHT', \RectorPrefix20220501\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_RIGHT')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v7\v6\WrapClickMenuOnIconRector::class);
};
