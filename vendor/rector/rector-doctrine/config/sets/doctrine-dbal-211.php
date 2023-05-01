<?php

declare (strict_types=1);
namespace RectorPrefix202305;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecations-in-the-wrapper-connection-class
        new MethodCallRename('Doctrine\\DBAL\\Connection', 'executeUpdate', 'executeStatement'),
        new MethodCallRename('Doctrine\\DBAL\\Connection', 'exec', 'executeStatement'),
        new MethodCallRename('Doctrine\\DBAL\\Connection', 'query', 'executeQuery'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#driverexceptiongeterrorcode-is-deprecated
        new MethodCallRename('Doctrine\\DBAL\\Driver\\DriverException', 'getErrorCode', 'getSQLState'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-expressionbuilder-methods
        new MethodCallRename('Doctrine\\DBAL\\Query\\Expression\\ExpressionBuilder', 'andX', 'and'),
        new MethodCallRename('Doctrine\\DBAL\\Query\\Expression\\ExpressionBuilder', 'orX', 'or'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-compositeexpression-methods
        new MethodCallRename('Doctrine\\DBAL\\Query\\Expression\\CompositeExpression', 'add', 'with'),
        new MethodCallRename('Doctrine\\DBAL\\Query\\Expression\\CompositeExpression', 'addMultiple', 'with'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-fetchmode-and-the-corresponding-methods
        new MethodCallRename('Doctrine\\DBAL\\Connection', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('Doctrine\\DBAL\\Connection', 'fetchArray', 'fetchNumeric'),
        new MethodCallRename('Doctrine\\DBAL\\Connection', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('Doctrine\\DBAL\\Connection', 'fetchAll', 'fetchAllAssociative'),
        new MethodCallRename('Doctrine\\DBAL\\Statement', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('Doctrine\\DBAL\\Statement', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('Doctrine\\DBAL\\Statement', 'fetchAll', 'fetchAllAssociative'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#pdo-related-classes-outside-of-the-pdo-namespace-are-deprecated
        'Doctrine\\DBAL\\Driver\\PDOMySql\\Driver' => 'Doctrine\\DBAL\\Driver\\PDO\\MySQL\\Driver',
        'Doctrine\\DBAL\\Driver\\PDOOracle\\Driver' => 'Doctrine\\DBAL\\Driver\\PDO\\OCI\\Driver',
        'Doctrine\\DBAL\\Driver\\PDOPgSql\\Driver' => 'Doctrine\\DBAL\\Driver\\PDO\\PgSQL\\Driver',
        'Doctrine\\DBAL\\Driver\\PDOSqlite\\Driver' => 'Doctrine\\DBAL\\Driver\\PDO\\SQLite\\Driver',
        'Doctrine\\DBAL\\Driver\\PDOSqlsrv\\Driver' => 'Doctrine\\DBAL\\Driver\\PDO\\SQLSrv\\Driver',
        'Doctrine\\DBAL\\Driver\\PDOSqlsrv\\Connection' => 'Doctrine\\DBAL\\Driver\\PDO\\SQLSrv\\Connection',
        'Doctrine\\DBAL\\Driver\\PDOSqlsrv\\Statement' => 'Doctrine\\DBAL\\Driver\\PDO\\SQLSrv\\Statement',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-dbalexception
        'Doctrine\\DBAL\\DBALException' => 'Doctrine\\DBAL\\Exception',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#inconsistently-and-ambiguously-named-driver-level-classes-are-deprecated
        'Doctrine\\DBAL\\Driver\\DriverException' => 'Doctrine\\DBAL\\Driver\\Exception',
        'Doctrine\\DBAL\\Driver\\AbstractDriverException' => 'Doctrine\\DBAL\\Driver\\AbstractException',
        'Doctrine\\DBAL\\Driver\\IBMDB2\\DB2Driver' => 'Doctrine\\DBAL\\Driver\\IBMDB2\\Driver',
        'Doctrine\\DBAL\\Driver\\IBMDB2\\DB2Connection' => 'Doctrine\\DBAL\\Driver\\IBMDB2\\Connection',
        'Doctrine\\DBAL\\Driver\\IBMDB2\\DB2Statement' => 'Doctrine\\DBAL\\Driver\\IBMDB2\\Statement',
        'Doctrine\\DBAL\\Driver\\Mysqli\\MysqliConnection' => 'Doctrine\\DBAL\\Driver\\Mysqli\\Connection',
        'Doctrine\\DBAL\\Driver\\Mysqli\\MysqliStatement' => 'Doctrine\\DBAL\\Driver\\Mysqli\\Statement',
        'Doctrine\\DBAL\\Driver\\OCI8\\OCI8Connection' => 'Doctrine\\DBAL\\Driver\\OCI8\\Connection',
        'Doctrine\\DBAL\\Driver\\OCI8\\OCI8Statement' => 'Doctrine\\DBAL\\Driver\\OCI8\\Statement',
        'Doctrine\\DBAL\\Driver\\SQLSrv\\SQLSrvConnection' => 'Doctrine\\DBAL\\Driver\\SQLSrv\\Connection',
        'Doctrine\\DBAL\\Driver\\SQLSrv\\SQLSrvStatement' => 'Doctrine\\DBAL\\Driver\\SQLSrv\\Statement',
        'Doctrine\\DBAL\\Driver\\PDOConnection' => 'Doctrine\\DBAL\\Driver\\PDO\\Connection',
        'Doctrine\\DBAL\\Driver\\PDOStatement' => 'Doctrine\\DBAL\\Driver\\PDO\\Statement',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-masterslaveconnection-use-primaryreadreplicaconnection
        'Doctrine\\DBAL\\Connections\\MasterSlaveConnection' => 'Doctrine\\DBAL\\Connections\\PrimaryReadReplicaConnection',
    ]);
};
