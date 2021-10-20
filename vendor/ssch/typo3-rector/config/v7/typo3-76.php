<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;
use Ssch\TYPO3Rector\Rector\v7\v6\RenamePiListBrowserResultsRector;
use Ssch\TYPO3Rector\Rector\v7\v6\WrapClickMenuOnIconRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
use RectorPrefix20211020\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v6\RenamePiListBrowserResultsRector::class);
    $services->set('document_template_issue_command_to_backend_utility_get_link_to_data_handler_action')->class(\Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector::class)->call('configure', [[\Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector::METHOD_CALLS_TO_STATIC_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Transform\ValueObject\MethodCallToStaticCall('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', 'issueCommand', 'TYPO3\\CMS\\Backend\\Utility\\BackendUtility', 'getLinkToDataHandlerAction')])]]);
    $services->set('search_form_controller_constants_to_like_wildcard_constants')->class(\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::class)->call('configure', [[\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_LEFT', \RectorPrefix20211020\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_LEFT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController', 'WILDCARD_RIGHT', \RectorPrefix20211020\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_RIGHT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_LEFT', \RectorPrefix20211020\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_LEFT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('TYPO3\\CMS\\IndexedSearch\\Domain\\Repository\\IndexSearchRepository', 'WILDCARD_RIGHT', \RectorPrefix20211020\TYPO3\CMS\IndexedSearch\Utility\LikeWildcard::class . '::WILDCARD_RIGHT')])]]);
    $services->set(\Ssch\TYPO3Rector\Rector\v7\v6\WrapClickMenuOnIconRector::class);
};
