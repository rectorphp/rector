<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-type-constants
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'TARRAY', 'Doctrine\\DBAL\\Types\\Types', 'ARRAY'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'SIMPLE_ARRAY', 'Doctrine\\DBAL\\Types\\Types', 'SIMPLE_ARRAY'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'JSON_ARRAY', 'Doctrine\\DBAL\\Types\\Types', 'JSON_ARRAY'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'JSON', 'Doctrine\\DBAL\\Types\\Types', 'JSON'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'BIGINT', 'Doctrine\\DBAL\\Types\\Types', 'BIGINT'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'BOOLEAN', 'Doctrine\\DBAL\\Types\\Types', 'BOOLEAN'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'DATETIME', 'Doctrine\\DBAL\\Types\\Types', 'DATETIME_MUTABLE'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'DATETIME_IMMUTABLE', 'Doctrine\\DBAL\\Types\\Types', 'DATETIME_IMMUTABLE'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'DATETIMETZ', 'Doctrine\\DBAL\\Types\\Types', 'DATETIMETZ_MUTABLE'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'DATETIMETZ_IMMUTABLE', 'Doctrine\\DBAL\\Types\\Types', 'DATETIMETZ_IMMUTABLE'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'DATE', 'Doctrine\\DBAL\\Types\\Types', 'DATE_MUTABLE'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'DATE_IMMUTABLE', 'Doctrine\\DBAL\\Types\\Types', 'DATE_IMMUTABLE'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'TIME', 'Doctrine\\DBAL\\Types\\Types', 'TIME_MUTABLE'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'TIME_IMMUTABLE', 'Doctrine\\DBAL\\Types\\Types', 'TIME_IMMUTABLE'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'DECIMAL', 'Doctrine\\DBAL\\Types\\Types', 'DECIMAL'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'INTEGER', 'Doctrine\\DBAL\\Types\\Types', 'INTEGER'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'OBJECT', 'Doctrine\\DBAL\\Types\\Types', 'OBJECT'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'SMALLINT', 'Doctrine\\DBAL\\Types\\Types', 'SMALLINT'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'STRING', 'Doctrine\\DBAL\\Types\\Types', 'STRING'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'TEXT', 'Doctrine\\DBAL\\Types\\Types', 'TEXT'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'BINARY', 'Doctrine\\DBAL\\Types\\Types', 'BINARY'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'BLOB', 'Doctrine\\DBAL\\Types\\Types', 'BLOB'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'FLOAT', 'Doctrine\\DBAL\\Types\\Types', 'FLOAT'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'GUID', 'Doctrine\\DBAL\\Types\\Types', 'GUID'), new RenameClassAndConstFetch('Doctrine\\DBAL\\Types\\Type', 'DATEINTERVAL', 'Doctrine\\DBAL\\Types\\Types', 'DATEINTERVAL')]);
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
