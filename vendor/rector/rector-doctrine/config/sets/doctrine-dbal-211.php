<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecations-in-the-wrapper-connection-class
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Connection', 'executeUpdate', 'executeStatement'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Connection', 'exec', 'executeStatement'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Connection', 'query', 'executeQuery'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#driverexceptiongeterrorcode-is-deprecated
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\DriverException', 'getErrorCode', 'getSQLState'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-expressionbuilder-methods
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Query\\Expression\\ExpressionBuilder', 'andX', 'and'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Query\\Expression\\ExpressionBuilder', 'orX', 'or'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-compositeexpression-methods
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Query\\Expression\\CompositeExpression', 'add', 'with'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Query\\Expression\\CompositeExpression', 'addMultiple', 'with'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-fetchmode-and-the-corresponding-methods
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Connection', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Connection', 'fetchArray', 'fetchNumeric'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Connection', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Connection', 'fetchAll', 'fetchAllAssociative'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Statement', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Statement', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Statement', 'fetchAll', 'fetchAllAssociative'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#pdo-related-classes-outside-of-the-pdo-namespace-are-deprecated
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDOMySql\\Driver' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDO\\MySQL\\Driver',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDOOracle\\Driver' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDO\\OCI\\Driver',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDOPgSql\\Driver' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDO\\PgSQL\\Driver',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDOSqlite\\Driver' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDO\\SQLite\\Driver',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDOSqlsrv\\Driver' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDO\\SQLSrv\\Driver',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDOSqlsrv\\Connection' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDO\\SQLSrv\\Connection',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDOSqlsrv\\Statement' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDO\\SQLSrv\\Statement',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-dbalexception
        'RectorPrefix20220607\\Doctrine\\DBAL\\DBALException' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Exception',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#inconsistently-and-ambiguously-named-driver-level-classes-are-deprecated
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\DriverException' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\Exception',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\AbstractDriverException' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\AbstractException',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\IBMDB2\\DB2Driver' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\IBMDB2\\Driver',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\IBMDB2\\DB2Connection' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\IBMDB2\\Connection',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\IBMDB2\\DB2Statement' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\IBMDB2\\Statement',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\Mysqli\\MysqliConnection' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\Mysqli\\Connection',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\Mysqli\\MysqliStatement' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\Mysqli\\Statement',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\OCI8\\OCI8Connection' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\OCI8\\Connection',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\OCI8\\OCI8Statement' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\OCI8\\Statement',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\SQLSrv\\SQLSrvConnection' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\SQLSrv\\Connection',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\SQLSrv\\SQLSrvStatement' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\SQLSrv\\Statement',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDOConnection' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDO\\Connection',
        'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDOStatement' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\PDO\\Statement',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-masterslaveconnection-use-primaryreadreplicaconnection
        'RectorPrefix20220607\\Doctrine\\DBAL\\Connections\\MasterSlaveConnection' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Connections\\PrimaryReadReplicaConnection',
    ]);
};
