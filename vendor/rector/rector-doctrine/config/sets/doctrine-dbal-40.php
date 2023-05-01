<?php

declare (strict_types=1);
namespace RectorPrefix202305;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\MethodCall\ChangeCompositeExpressionAddMultipleWithWithRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-compositeexpression-methods
    $rectorConfig->rule(ChangeCompositeExpressionAddMultipleWithWithRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-misspelled-isfullfilledby-method
        new MethodCallRename('Doctrine\\DBAL\\Schema\\Index', 'isFullfilledBy', 'isFulfilledBy'),
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-expressionbuilder-methods
        new MethodCallRename('Doctrine\\DBAL\\Query\\Expression\\ExpressionBuilder', 'andX', 'and'),
        new MethodCallRename('Doctrine\\DBAL\\Query\\Expression\\ExpressionBuilder', 'orX', 'or'),
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-compositeexpression-methods
        new MethodCallRename('Doctrine\\DBAL\\Query\\Expression\\CompositeExpression', 'add', 'with'),
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removal-of-doctrine-cache
        new MethodCallRename('Doctrine\\DBAL\\Configuration', 'setResultCacheImpl', 'setResultCache'),
        new MethodCallRename('Doctrine\\DBAL\\Configuration', 'getResultCacheImpl', 'getResultCache'),
        new MethodCallRename('Doctrine\\DBAL\\QueryCacheProfile', 'setResultCacheDriver', 'setResultCache'),
        new MethodCallRename('Doctrine\\DBAL\\QueryCacheProfile', 'getResultCacheDriver', 'getResultCache'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-renamed-sqlite-platform-classes
        'Doctrine\\DBAL\\Platforms\\SqlitePlatform' => 'Doctrine\\DBAL\\Platforms\\SQLitePlatform',
        'Doctrine\\DBAL\\Schema\\SqliteSchemaManager' => 'Doctrine\\DBAL\\Schema\\SQLiteSchemaManager',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connectionparam__array-constants
        new RenameClassAndConstFetch('Doctrine\\DBAL\\Connection', 'PARAM_STR_ARRAY', 'Doctrine\\DBAL\\ArrayParameterType', 'STRING'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connectionparam__array-constants
        new RenameClassAndConstFetch('Doctrine\\DBAL\\Connection', 'PARAM_INT_ARRAY', 'Doctrine\\DBAL\\ArrayParameterType', 'INTEGER'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connection_schemamanager-and-connectiongetschemamanager
        new MethodCallRename('Doctrine\\DBAL\\Connection', 'getSchemaManager', 'createSchemaManager'),
    ]);
};
