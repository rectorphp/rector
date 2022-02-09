<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Ssch\TYPO3Rector\Rector\v7\v6\RenamePiListBrowserResultsRector;
use Ssch\TYPO3Rector\Rector\v7\v6\WrapClickMenuOnIconRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220209\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v6\RenamePiListBrowserResultsRector::class);
    $services->set(\Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector::class)->configure([new \Rector\Transform\ValueObject\MethodCallToStaticCall('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'issueCommand', 'TYPO3\\CMS\\Backend\\Utility\\BackendUtility', 'getLinkToDataHandlerAction')]);
    $services->set(\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::class)->configure([new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_LEFT', \RectorPrefix20220209\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_LEFT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_RIGHT', \RectorPrefix20220209\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_RIGHT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_LEFT', \RectorPrefix20220209\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_LEFT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_RIGHT', \RectorPrefix20220209\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_RIGHT')]);
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v6\WrapClickMenuOnIconRector::class);
};
