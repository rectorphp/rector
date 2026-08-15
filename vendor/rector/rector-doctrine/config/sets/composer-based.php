<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VoidType;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Bundle210\Rector\Class_\EventSubscriberInterfaceToAttributeRector;
use Rector\Doctrine\Bundle230\Rector\Class_\AddAnnotationToRepositoryRector;
use Rector\Doctrine\Collection22\Rector\CriteriaOrderingConstantsDeprecationRector;
use Rector\Doctrine\Dbal211\Rector\MethodCall\ExtractArrayArgOnQueryBuilderSelectRector;
use Rector\Doctrine\Dbal211\Rector\MethodCall\ReplaceFetchAllMethodCallRector;
use Rector\Doctrine\Dbal31\Rector\MethodCall\QueryBuilderExecuteToExecuteQueryOrExecuteStatementRector;
use Rector\Doctrine\Dbal36\Rector\MethodCall\MigrateQueryBuilderResetQueryPartRector;
use Rector\Doctrine\Dbal40\Rector\MethodCall\ChangeCompositeExpressionAddMultipleWithWithRector;
use Rector\Doctrine\Dbal40\Rector\StmtsAwareInterface\ExecuteQueryParamsToBindValueRector;
use Rector\Doctrine\Dbal42\Rector\New_\AddArrayResultColumnNamesRector;
use Rector\Doctrine\DoctrineFixture\Rector\MethodCall\AddGetReferenceTypeRector;
use Rector\Doctrine\Orm214\Rector\Param\ReplaceLifecycleEventArgsByDedicatedEventArgsRector;
use Rector\Doctrine\Orm28\Rector\MethodCall\IterateToToIterableRector;
use Rector\Doctrine\Orm30\Rector\MethodCall\CastDoctrineExprToStringRector;
use Rector\Doctrine\Orm30\Rector\MethodCall\SetParametersArrayToCollectionRector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Property\NestedAnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationPropertyToAttributeClass;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\NestedAnnotationToAttribute;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Renaming\Rector\Class_\RenameAttributeRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameAttribute;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
/**
 * Rules bound to the Doctrine package version installed in the analysed project,
 * as not every attribute, class and method exists in every Doctrine version.
 *
 * Thanks to the composer package constraint, these rules can be registered once here, instead of being
 * repeated in every Doctrine version set to cover a direct upgrade from an older version.
 */
return static function (RectorConfig $rectorConfig): void {
    // each of these rules declares the exact Doctrine package and the version its target API was added in,
    // @see ComposerPackageConstraintInterface
    $rectorConfig->rules([
        // doctrine/orm 2.8, 2.14 and 3.0
        IterateToToIterableRector::class,
        ReplaceLifecycleEventArgsByDedicatedEventArgsRector::class,
        SetParametersArrayToCollectionRector::class,
        CastDoctrineExprToStringRector::class,
        // doctrine/collections 2.2
        CriteriaOrderingConstantsDeprecationRector::class,
        // doctrine/dbal 2.11
        ExtractArrayArgOnQueryBuilderSelectRector::class,
        ReplaceFetchAllMethodCallRector::class,
        // doctrine/dbal 3.8
        MigrateQueryBuilderResetQueryPartRector::class,
        // doctrine/dbal 3.1
        QueryBuilderExecuteToExecuteQueryOrExecuteStatementRector::class,
        // doctrine/dbal 4.0 and 4.2
        ChangeCompositeExpressionAddMultipleWithWithRector::class,
        ExecuteQueryParamsToBindValueRector::class,
        AddArrayResultColumnNamesRector::class,
        // doctrine/doctrine-bundle 2.3 and 2.8
        AddAnnotationToRepositoryRector::class,
        EventSubscriberInterfaceToAttributeRector::class,
        // doctrine/data-fixtures 1.6
        AddGetReferenceTypeRector::class,
    ]);
    // doctrine/data-fixtures 1.7
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Doctrine\Common\DataFixtures\OrderedFixtureInterface', 'getOrder', new IntegerType())], 'doctrine/data-fixtures', '>=1.7');
    // doctrine/common 2.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Doctrine\Common\Persistence\Event\LifecycleEventArgs' => 'Doctrine\Persistence\Event\LifecycleEventArgs', 'Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs' => 'Doctrine\Persistence\Event\LoadClassMetadataEventArgs', 'Doctrine\Common\Persistence\Event\ManagerEventArgs' => 'Doctrine\Persistence\Event\ManagerEventArgs', 'Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory' => 'Doctrine\Persistence\Mapping\AbstractClassMetadataFactory', 'Doctrine\Common\Persistence\Mapping\ClassMetadata' => 'Doctrine\Persistence\Mapping\ClassMetadata', 'Doctrine\Common\Persistence\Mapping\ClassMetadataFactory' => 'Doctrine\Persistence\Mapping\ClassMetadataFactory', 'Doctrine\Common\Persistence\Mapping\Driver\FileDriver' => 'Doctrine\Persistence\Mapping\Driver\FileDriver', 'Doctrine\Common\Persistence\Mapping\Driver\MappingDriver' => 'Doctrine\Persistence\Mapping\Driver\MappingDriver', 'Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain' => 'Doctrine\Persistence\Mapping\Driver\MappingDriverChain', 'Doctrine\Common\Persistence\Mapping\Driver\PHPDriver' => 'Doctrine\Persistence\Mapping\Driver\PHPDriver', 'Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver' => 'Doctrine\Persistence\Mapping\Driver\StaticPHPDriver', 'Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator' => 'Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator', 'Doctrine\Common\Persistence\Mapping\MappingException' => 'Doctrine\Persistence\Mapping\MappingException', 'Doctrine\Common\Persistence\Mapping\ReflectionService' => 'Doctrine\Persistence\Mapping\ReflectionService', 'Doctrine\Common\Persistence\Mapping\RuntimeReflectionService' => 'Doctrine\Persistence\Mapping\RuntimeReflectionService', 'Doctrine\Common\Persistence\Mapping\StaticReflectionService' => 'Doctrine\Persistence\Mapping\StaticReflectionService', 'Doctrine\Common\Persistence\ObjectManager' => 'Doctrine\Persistence\ObjectManager', 'Doctrine\Common\Persistence\ObjectManagerDecorator' => 'Doctrine\Persistence\ObjectManagerDecorator', 'Doctrine\Common\Persistence\ObjectRepository' => 'Doctrine\Persistence\ObjectRepository', 'Doctrine\Common\Persistence\Proxy' => 'Doctrine\Persistence\Proxy', 'Doctrine\Common\Persistence\AbstractManagerRegistry' => 'Doctrine\Persistence\AbstractManagerRegistry', 'Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator' => 'Doctrine\Persistence\Mapping\Driver\DefaultFileLocator', 'Doctrine\Common\Persistence\ManagerRegistry' => 'Doctrine\Persistence\ManagerRegistry'], 'doctrine/common', '>=2.0');
    // doctrine/orm 2.5
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Doctrine\ORM\Mapping\ClassMetadataFactory', 'setEntityManager', 0, new ObjectType('Doctrine\ORM\EntityManagerInterface')), new AddParamTypeDeclaration('Doctrine\ORM\Tools\DebugUnitOfWorkListener', 'dumpIdentityMap', 0, new ObjectType('Doctrine\ORM\EntityManagerInterface'))], 'doctrine/orm', '>=2.5');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(ArgumentRemoverRector::class, [new ArgumentRemover('Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister', 'getSelectJoinColumnSQL', 4, null)], 'doctrine/orm', '>=2.5');
    // doctrine/dbal 2.11
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'TARRAY', 'Doctrine\DBAL\Types\Types', 'ARRAY'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'SIMPLE_ARRAY', 'Doctrine\DBAL\Types\Types', 'SIMPLE_ARRAY'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'JSON_ARRAY', 'Doctrine\DBAL\Types\Types', 'JSON_ARRAY'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'JSON', 'Doctrine\DBAL\Types\Types', 'JSON'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'BIGINT', 'Doctrine\DBAL\Types\Types', 'BIGINT'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'BOOLEAN', 'Doctrine\DBAL\Types\Types', 'BOOLEAN'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'DATETIME', 'Doctrine\DBAL\Types\Types', 'DATETIME_MUTABLE'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'DATETIME_IMMUTABLE', 'Doctrine\DBAL\Types\Types', 'DATETIME_IMMUTABLE'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'DATETIMETZ', 'Doctrine\DBAL\Types\Types', 'DATETIMETZ_MUTABLE'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'DATETIMETZ_IMMUTABLE', 'Doctrine\DBAL\Types\Types', 'DATETIMETZ_IMMUTABLE'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'DATE', 'Doctrine\DBAL\Types\Types', 'DATE_MUTABLE'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'DATE_IMMUTABLE', 'Doctrine\DBAL\Types\Types', 'DATE_IMMUTABLE'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'TIME', 'Doctrine\DBAL\Types\Types', 'TIME_MUTABLE'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'TIME_IMMUTABLE', 'Doctrine\DBAL\Types\Types', 'TIME_IMMUTABLE'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'DECIMAL', 'Doctrine\DBAL\Types\Types', 'DECIMAL'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'INTEGER', 'Doctrine\DBAL\Types\Types', 'INTEGER'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'OBJECT', 'Doctrine\DBAL\Types\Types', 'OBJECT'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'SMALLINT', 'Doctrine\DBAL\Types\Types', 'SMALLINT'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'STRING', 'Doctrine\DBAL\Types\Types', 'STRING'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'TEXT', 'Doctrine\DBAL\Types\Types', 'TEXT'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'BINARY', 'Doctrine\DBAL\Types\Types', 'BINARY'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'BLOB', 'Doctrine\DBAL\Types\Types', 'BLOB'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'FLOAT', 'Doctrine\DBAL\Types\Types', 'FLOAT'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'GUID', 'Doctrine\DBAL\Types\Types', 'GUID'), new RenameClassAndConstFetch('Doctrine\DBAL\Types\Type', 'DATEINTERVAL', 'Doctrine\DBAL\Types\Types', 'DATEINTERVAL')], 'doctrine/dbal', '>=2.11');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#pdo-related-classes-outside-of-the-pdo-namespace-are-deprecated
        'Doctrine\DBAL\Driver\PDOMySql\Driver' => 'Doctrine\DBAL\Driver\PDO\MySQL\Driver',
        'Doctrine\DBAL\Driver\PDOOracle\Driver' => 'Doctrine\DBAL\Driver\PDO\OCI\Driver',
        'Doctrine\DBAL\Driver\PDOPgSql\Driver' => 'Doctrine\DBAL\Driver\PDO\PgSQL\Driver',
        'Doctrine\DBAL\Driver\PDOSqlite\Driver' => 'Doctrine\DBAL\Driver\PDO\SQLite\Driver',
        'Doctrine\DBAL\Driver\PDOSqlsrv\Driver' => 'Doctrine\DBAL\Driver\PDO\SQLSrv\Driver',
        'Doctrine\DBAL\Driver\PDOSqlsrv\Connection' => 'Doctrine\DBAL\Driver\PDO\SQLSrv\Connection',
        'Doctrine\DBAL\Driver\PDOSqlsrv\Statement' => 'Doctrine\DBAL\Driver\PDO\SQLSrv\Statement',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-dbalexception
        'Doctrine\DBAL\DBALException' => 'Doctrine\DBAL\Exception',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#inconsistently-and-ambiguously-named-driver-level-classes-are-deprecated
        'Doctrine\DBAL\Driver\DriverException' => 'Doctrine\DBAL\Driver\Exception',
        'Doctrine\DBAL\Driver\AbstractDriverException' => 'Doctrine\DBAL\Driver\AbstractException',
        'Doctrine\DBAL\Driver\IBMDB2\DB2Driver' => 'Doctrine\DBAL\Driver\IBMDB2\Driver',
        'Doctrine\DBAL\Driver\IBMDB2\DB2Connection' => 'Doctrine\DBAL\Driver\IBMDB2\Connection',
        'Doctrine\DBAL\Driver\IBMDB2\DB2Statement' => 'Doctrine\DBAL\Driver\IBMDB2\Statement',
        'Doctrine\DBAL\Driver\Mysqli\MysqliConnection' => 'Doctrine\DBAL\Driver\Mysqli\Connection',
        'Doctrine\DBAL\Driver\Mysqli\MysqliStatement' => 'Doctrine\DBAL\Driver\Mysqli\Statement',
        'Doctrine\DBAL\Driver\OCI8\OCI8Connection' => 'Doctrine\DBAL\Driver\OCI8\Connection',
        'Doctrine\DBAL\Driver\OCI8\OCI8Statement' => 'Doctrine\DBAL\Driver\OCI8\Statement',
        'Doctrine\DBAL\Driver\SQLSrv\SQLSrvConnection' => 'Doctrine\DBAL\Driver\SQLSrv\Connection',
        'Doctrine\DBAL\Driver\SQLSrv\SQLSrvStatement' => 'Doctrine\DBAL\Driver\SQLSrv\Statement',
        'Doctrine\DBAL\Driver\PDOConnection' => 'Doctrine\DBAL\Driver\PDO\Connection',
        'Doctrine\DBAL\Driver\PDOStatement' => 'Doctrine\DBAL\Driver\PDO\Statement',
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-masterslaveconnection-use-primaryreadreplicaconnection
        'Doctrine\DBAL\Connections\MasterSlaveConnection' => 'Doctrine\DBAL\Connections\PrimaryReadReplicaConnection',
    ], 'doctrine/dbal', '>=2.11');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecations-in-the-wrapper-connection-class
        new MethodCallRename('Doctrine\DBAL\Connection', 'executeUpdate', 'executeStatement'),
        new MethodCallRename('Doctrine\DBAL\Connection', 'exec', 'executeStatement'),
        new MethodCallRename('Doctrine\DBAL\Connection', 'query', 'executeQuery'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#driverexceptiongeterrorcode-is-deprecated
        new MethodCallRename('Doctrine\DBAL\Driver\DriverException', 'getErrorCode', 'getSQLState'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-expressionbuilder-methods
        new MethodCallRename('Doctrine\DBAL\Query\Expression\ExpressionBuilder', 'andX', 'and'),
        new MethodCallRename('Doctrine\DBAL\Query\Expression\ExpressionBuilder', 'orX', 'or'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-compositeexpression-methods
        new MethodCallRename('Doctrine\DBAL\Query\Expression\CompositeExpression', 'add', 'with'),
        new MethodCallRename('Doctrine\DBAL\Query\Expression\CompositeExpression', 'addMultiple', 'with'),
        // https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-fetchmode-and-the-corresponding-methods
        new MethodCallRename('Doctrine\DBAL\Connection', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('Doctrine\DBAL\Connection', 'fetchArray', 'fetchNumeric'),
        new MethodCallRename('Doctrine\DBAL\Connection', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('Doctrine\DBAL\Connection', 'fetchAll', 'fetchAllAssociative'),
        new MethodCallRename('Doctrine\DBAL\Statement', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('Doctrine\DBAL\Statement', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('Doctrine\DBAL\Statement', 'fetchAll', 'fetchAllAssociative'),
        new MethodCallRename('Doctrine\DBAL\Result', 'fetchAssoc', 'fetchAssociative'),
        new MethodCallRename('Doctrine\DBAL\Result', 'fetchArray', 'fetchNumeric'),
        new MethodCallRename('Doctrine\DBAL\Result', 'fetchColumn', 'fetchOne'),
        new MethodCallRename('Doctrine\DBAL\Result', 'fetchAll', 'fetchAllAssociative'),
    ], 'doctrine/dbal', '>=2.11');
    // doctrine/orm 2.13
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/doctrine/orm/pull/9906
        'Doctrine\ORM\Event\LifecycleEventArgs' => 'Doctrine\Persistence\Event\LifecycleEventArgs',
    ], 'doctrine/orm', '>=2.13');
    // doctrine/orm 3.5
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/doctrine/orm/pull/12022
        new MethodCallRename('Doctrine\ORM\ORMSetup', 'createAttributeMetadataConfiguration', 'createAttributeMetadataConfig'),
        new MethodCallRename('Doctrine\ORM\ORMSetup', 'createXMLMetadataConfiguration', 'createXMLMetadataConfig'),
        new MethodCallRename('Doctrine\ORM\ORMSetup', 'createConfiguration', 'createConfig'),
    ], 'doctrine/orm', '>=3.5');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/doctrine/orm/pull/9876
        new MethodCallRename('Doctrine\ORM\Event\LifecycleEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Doctrine\ORM\Event\OnClearEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Doctrine\ORM\Event\OnFlushEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Doctrine\ORM\Event\PostFlushEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Doctrine\ORM\Event\PreFlushEventArgs', 'getEntityManager', 'getObjectManager'),
        // @see https://github.com/doctrine/orm/pull/9906
        new MethodCallRename('Doctrine\ORM\Event\LifecycleEventArgs', 'getEntity', 'getObject'),
        // @see https://github.com/doctrine/dbal/pull/4580
        new MethodCallRename('Doctrine\DBAL\Statement', 'execute', 'executeQuery'),
    ], 'doctrine/orm', '>=2.13');
    // doctrine/mongodb-odm 2.16
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameAttributeRector::class, [new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\ChunkSize', 'Doctrine\ODM\MongoDB\Mapping\Attribute\File\ChunkSize'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\Filename', 'Doctrine\ODM\MongoDB\Mapping\Attribute\File\Filename'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\Length', 'Doctrine\ODM\MongoDB\Mapping\Attribute\File\Length'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\Metadata', 'Doctrine\ODM\MongoDB\Mapping\Attribute\File\Metadata'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\UploadDate', 'Doctrine\ODM\MongoDB\Mapping\Attribute\File\UploadDate'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\AlsoLoad', 'Doctrine\ODM\MongoDB\Mapping\Attribute\AlsoLoad'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ChangeTrackingPolicy', 'Doctrine\ODM\MongoDB\Mapping\Attribute\ChangeTrackingPolicy'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\DefaultDiscriminatorValue', 'Doctrine\ODM\MongoDB\Mapping\Attribute\DefaultDiscriminatorValue'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\DiscriminatorField', 'Doctrine\ODM\MongoDB\Mapping\Attribute\DiscriminatorField'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\DiscriminatorMap', 'Doctrine\ODM\MongoDB\Mapping\Attribute\DiscriminatorMap'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\DiscriminatorValue', 'Doctrine\ODM\MongoDB\Mapping\Attribute\DiscriminatorValue'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Document', 'Doctrine\ODM\MongoDB\Mapping\Attribute\Document'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument', 'Doctrine\ODM\MongoDB\Mapping\Attribute\EmbeddedDocument'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany', 'Doctrine\ODM\MongoDB\Mapping\Attribute\EmbedMany'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne', 'Doctrine\ODM\MongoDB\Mapping\Attribute\EmbedOne'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Encrypt', 'Doctrine\ODM\MongoDB\Mapping\Attribute\Encrypt'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Field', 'Doctrine\ODM\MongoDB\Mapping\Attribute\Field'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File', 'Doctrine\ODM\MongoDB\Mapping\Attribute\File'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks', 'Doctrine\ODM\MongoDB\Mapping\Attribute\HasLifecycleCallbacks'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Id', 'Doctrine\ODM\MongoDB\Mapping\Attribute\Id'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Index', 'Doctrine\ODM\MongoDB\Mapping\Attribute\Index'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\InheritanceType', 'Doctrine\ODM\MongoDB\Mapping\Attribute\InheritanceType'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Lock', 'Doctrine\ODM\MongoDB\Mapping\Attribute\Lock'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\MappedSuperclass', 'Doctrine\ODM\MongoDB\Mapping\Attribute\MappedSuperclass'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PostLoad', 'Doctrine\ODM\MongoDB\Mapping\Attribute\PostLoad'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PostPersist', 'Doctrine\ODM\MongoDB\Mapping\Attribute\PostPersist'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PostRemove', 'Doctrine\ODM\MongoDB\Mapping\Attribute\PostRemove'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PostUpdate', 'Doctrine\ODM\MongoDB\Mapping\Attribute\PostUpdate'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PreFlush', 'Doctrine\ODM\MongoDB\Mapping\Attribute\PreFlush'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PreLoad', 'Doctrine\ODM\MongoDB\Mapping\Attribute\PreLoad'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PrePersist', 'Doctrine\ODM\MongoDB\Mapping\Attribute\PrePersist'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PreRemove', 'Doctrine\ODM\MongoDB\Mapping\Attribute\PreRemove'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PreUpdate', 'Doctrine\ODM\MongoDB\Mapping\Attribute\PreUpdate'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\QueryResultDocument', 'Doctrine\ODM\MongoDB\Mapping\Attribute\QueryResultDocument'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ReadPreference', 'Doctrine\ODM\MongoDB\Mapping\Attribute\ReadPreference'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany', 'Doctrine\ODM\MongoDB\Mapping\Attribute\ReferenceMany'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne', 'Doctrine\ODM\MongoDB\Mapping\Attribute\ReferenceOne'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\SearchIndex', 'Doctrine\ODM\MongoDB\Mapping\Attribute\SearchIndex'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ShardKey', 'Doctrine\ODM\MongoDB\Mapping\Attribute\ShardKey'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\TimeSeries', 'Doctrine\ODM\MongoDB\Mapping\Attribute\TimeSeries'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\UniqueIndex', 'Doctrine\ODM\MongoDB\Mapping\Attribute\UniqueIndex'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Validation', 'Doctrine\ODM\MongoDB\Mapping\Attribute\Validation'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\VectorSearchIndex', 'Doctrine\ODM\MongoDB\Mapping\Attribute\VectorSearchIndex'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Version', 'Doctrine\ODM\MongoDB\Mapping\Attribute\Version'), new RenameAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\View', 'Doctrine\ODM\MongoDB\Mapping\Attribute\View')], 'doctrine/mongodb-odm', '>=2.16');
    // doctrine/orm 2.19
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_NONE', 'Doctrine\ORM\Query\TokenType', 'T_NONE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_INTEGER', 'Doctrine\ORM\Query\TokenType', 'T_INTEGER'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_STRING', 'Doctrine\ORM\Query\TokenType', 'T_STRING'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_INPUT_PARAMETER', 'Doctrine\ORM\Query\TokenType', 'T_INPUT_PARAMETER'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_FLOAT', 'Doctrine\ORM\Query\TokenType', 'T_FLOAT'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_CLOSE_PARENTHESIS', 'Doctrine\ORM\Query\TokenType', 'T_CLOSE_PARENTHESIS'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_OPEN_PARENTHESIS', 'Doctrine\ORM\Query\TokenType', 'T_OPEN_PARENTHESIS'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_COMMA', 'Doctrine\ORM\Query\TokenType', 'T_COMMA'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_DIVIDE', 'Doctrine\ORM\Query\TokenType', 'T_DIVIDE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_DOT', 'Doctrine\ORM\Query\TokenType', 'T_DOT'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_EQUALS', 'Doctrine\ORM\Query\TokenType', 'T_EQUALS'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_GREATER_THAN', 'Doctrine\ORM\Query\TokenType', 'T_GREATER_THAN'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_LOWER_THAN', 'Doctrine\ORM\Query\TokenType', 'T_LOWER_THAN'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_MINUS', 'Doctrine\ORM\Query\TokenType', 'T_MINUS'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_MULTIPLY', 'Doctrine\ORM\Query\TokenType', 'T_MULTIPLY'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_NEGATE', 'Doctrine\ORM\Query\TokenType', 'T_NEGATE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_PLUS', 'Doctrine\ORM\Query\TokenType', 'T_PLUS'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_OPEN_CURLY_BRACE', 'Doctrine\ORM\Query\TokenType', 'T_OPEN_CURLY_BRACE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_CLOSE_CURLY_BRACE', 'Doctrine\ORM\Query\TokenType', 'T_CLOSE_CURLY_BRACE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_ALIASED_NAME', 'Doctrine\ORM\Query\TokenType', 'T_ALIASED_NAME'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_FULLY_QUALIFIED_NAME', 'Doctrine\ORM\Query\TokenType', 'T_FULLY_QUALIFIED_NAME'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_IDENTIFIER', 'Doctrine\ORM\Query\TokenType', 'T_IDENTIFIER'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_ALL', 'Doctrine\ORM\Query\TokenType', 'T_ALL'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_AND', 'Doctrine\ORM\Query\TokenType', 'T_AND'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_ANY', 'Doctrine\ORM\Query\TokenType', 'T_ANY'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_AS', 'Doctrine\ORM\Query\TokenType', 'T_AS'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_ASC', 'Doctrine\ORM\Query\TokenType', 'T_ASC'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_AVG', 'Doctrine\ORM\Query\TokenType', 'T_AVG'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_BETWEEN', 'Doctrine\ORM\Query\TokenType', 'T_BETWEEN'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_BOTH', 'Doctrine\ORM\Query\TokenType', 'T_BOTH'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_BY', 'Doctrine\ORM\Query\TokenType', 'T_BY'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_CASE', 'Doctrine\ORM\Query\TokenType', 'T_CASE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_COALESCE', 'Doctrine\ORM\Query\TokenType', 'T_COALESCE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_COUNT', 'Doctrine\ORM\Query\TokenType', 'T_COUNT'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_DELETE', 'Doctrine\ORM\Query\TokenType', 'T_DELETE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_DESC', 'Doctrine\ORM\Query\TokenType', 'T_DESC'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_DISTINCT', 'Doctrine\ORM\Query\TokenType', 'T_DISTINCT'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_ELSE', 'Doctrine\ORM\Query\TokenType', 'T_ELSE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_EMPTY', 'Doctrine\ORM\Query\TokenType', 'T_EMPTY'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_END', 'Doctrine\ORM\Query\TokenType', 'T_END'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_ESCAPE', 'Doctrine\ORM\Query\TokenType', 'T_ESCAPE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_EXISTS', 'Doctrine\ORM\Query\TokenType', 'T_EXISTS'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_FALSE', 'Doctrine\ORM\Query\TokenType', 'T_FALSE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_FROM', 'Doctrine\ORM\Query\TokenType', 'T_FROM'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_GROUP', 'Doctrine\ORM\Query\TokenType', 'T_GROUP'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_HAVING', 'Doctrine\ORM\Query\TokenType', 'T_HAVING'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_HIDDEN', 'Doctrine\ORM\Query\TokenType', 'T_HIDDEN'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_IN', 'Doctrine\ORM\Query\TokenType', 'T_IN'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_INDEX', 'Doctrine\ORM\Query\TokenType', 'T_INDEX'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_INNER', 'Doctrine\ORM\Query\TokenType', 'T_INNER'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_INSTANCE', 'Doctrine\ORM\Query\TokenType', 'T_INSTANCE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_IS', 'Doctrine\ORM\Query\TokenType', 'T_IS'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_JOIN', 'Doctrine\ORM\Query\TokenType', 'T_JOIN'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_LEADING', 'Doctrine\ORM\Query\TokenType', 'T_LEADING'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_LEFT', 'Doctrine\ORM\Query\TokenType', 'T_LEFT'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_LIKE', 'Doctrine\ORM\Query\TokenType', 'T_LIKE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_MAX', 'Doctrine\ORM\Query\TokenType', 'T_MAX'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_MEMBER', 'Doctrine\ORM\Query\TokenType', 'T_MEMBER'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_MIN', 'Doctrine\ORM\Query\TokenType', 'T_MIN'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_NEW', 'Doctrine\ORM\Query\TokenType', 'T_NEW'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_NOT', 'Doctrine\ORM\Query\TokenType', 'T_NOT'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_NULL', 'Doctrine\ORM\Query\TokenType', 'T_NULL'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_NULLIF', 'Doctrine\ORM\Query\TokenType', 'T_NULLIF'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_OF', 'Doctrine\ORM\Query\TokenType', 'T_OF'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_OR', 'Doctrine\ORM\Query\TokenType', 'T_OR'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_ORDER', 'Doctrine\ORM\Query\TokenType', 'T_ORDER'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_OUTER', 'Doctrine\ORM\Query\TokenType', 'T_OUTER'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_PARTIAL', 'Doctrine\ORM\Query\TokenType', 'T_PARTIAL'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_SELECT', 'Doctrine\ORM\Query\TokenType', 'T_SELECT'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_SET', 'Doctrine\ORM\Query\TokenType', 'T_SET'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_SOME', 'Doctrine\ORM\Query\TokenType', 'T_SOME'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_SUM', 'Doctrine\ORM\Query\TokenType', 'T_SUM'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_THEN', 'Doctrine\ORM\Query\TokenType', 'T_THEN'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_TRAILING', 'Doctrine\ORM\Query\TokenType', 'T_TRAILING'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_TRUE', 'Doctrine\ORM\Query\TokenType', 'T_TRUE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_UPDATE', 'Doctrine\ORM\Query\TokenType', 'T_UPDATE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_WHEN', 'Doctrine\ORM\Query\TokenType', 'T_WHEN'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_WHERE', 'Doctrine\ORM\Query\TokenType', 'T_WHERE'), new RenameClassAndConstFetch('Doctrine\ORM\Query\Lexer', 'T_WITH', 'Doctrine\ORM\Query\TokenType', 'T_WITH')], 'doctrine/orm', '>=2.19');
    // doctrine/dbal 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Doctrine\DBAL\Connection', 'ping', new VoidType())], 'doctrine/dbal', '>=3.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Doctrine\DBAL\Abstraction\Result' => 'Doctrine\DBAL\Result'], 'doctrine/dbal', '>=3.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [new MethodCallRename('Doctrine\DBAL\Platforms\AbstractPlatform', 'getVarcharTypeDeclarationSQL', 'getStringTypeDeclarationSQL'), new MethodCallRename('Doctrine\DBAL\Driver\DriverException', 'getErrorCode', 'getCode')], 'doctrine/dbal', '>=3.0');
    // doctrine/orm 3.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, ['Doctrine\ORM\ORMException' => 'Doctrine\ORM\Exception\ORMException'], 'doctrine/orm', '>=3.0');
    // doctrine/dbal 4.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connectionparam__array-constants
        new RenameClassAndConstFetch('Doctrine\DBAL\Connection', 'PARAM_STR_ARRAY', 'Doctrine\DBAL\ArrayParameterType', 'STRING'),
    ], 'doctrine/dbal', '>=4.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connectionparam__array-constants
        new RenameClassAndConstFetch('Doctrine\DBAL\Connection', 'PARAM_INT_ARRAY', 'Doctrine\DBAL\ArrayParameterType', 'INTEGER'),
    ], 'doctrine/dbal', '>=4.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassConstFetchRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.4.x/UPGRADE.md#bc-break-removed-array-and-object-column-types
        new RenameClassAndConstFetch('Doctrine\DBAL\Types\Types', 'ARRAY', 'Doctrine\DBAL\Types\Types', 'SIMPLE_ARRAY'),
    ], 'doctrine/dbal', '>=4.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameClassRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-renamed-sqlite-platform-classes
        'Doctrine\DBAL\Platforms\SqlitePlatform' => 'Doctrine\DBAL\Platforms\SQLitePlatform',
        'Doctrine\DBAL\Schema\SqliteSchemaManager' => 'Doctrine\DBAL\Schema\SQLiteSchemaManager',
    ], 'doctrine/dbal', '>=4.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-misspelled-isfullfilledby-method
        new MethodCallRename('Doctrine\DBAL\Schema\Index', 'isFullfilledBy', 'isFulfilledBy'),
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-expressionbuilder-methods
        new MethodCallRename('Doctrine\DBAL\Query\Expression\ExpressionBuilder', 'andX', 'and'),
        new MethodCallRename('Doctrine\DBAL\Query\Expression\ExpressionBuilder', 'orX', 'or'),
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-compositeexpression-methods
        new MethodCallRename('Doctrine\DBAL\Query\Expression\CompositeExpression', 'add', 'with'),
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removal-of-doctrine-cache
        new MethodCallRename('Doctrine\DBAL\Configuration', 'setResultCacheImpl', 'setResultCache'),
        new MethodCallRename('Doctrine\DBAL\Configuration', 'getResultCacheImpl', 'getResultCache'),
        new MethodCallRename('Doctrine\DBAL\QueryCacheProfile', 'setResultCacheDriver', 'setResultCache'),
        new MethodCallRename('Doctrine\DBAL\QueryCacheProfile', 'getResultCacheDriver', 'getResultCache'),
    ], 'doctrine/dbal', '>=4.0');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(RenameMethodRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connection_schemamanager-and-connectiongetschemamanager
        new MethodCallRename('Doctrine\DBAL\Connection', 'getSchemaManager', 'createSchemaManager'),
    ], 'doctrine/dbal', '>=4.0');
    // doctrine/orm 2.9, the first version to ship mapping classes as PHP 8 attributes
    $rectorConfig->ruleWithConfigurationComposerVersionBound(NestedAnnotationToAttributeRector::class, [
        /** @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.13/reference/attributes-reference.html#joincolumn-inversejoincolumn */
        new NestedAnnotationToAttribute('Doctrine\ORM\Mapping\JoinTable', [new AnnotationPropertyToAttributeClass('Doctrine\ORM\Mapping\JoinColumn', 'joinColumns'), new AnnotationPropertyToAttributeClass('Doctrine\ORM\Mapping\InverseJoinColumn', 'inverseJoinColumns', \true)]),
        /** @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html#joincolumns */
        new NestedAnnotationToAttribute('Doctrine\ORM\Mapping\JoinColumns', [new AnnotationPropertyToAttributeClass('Doctrine\ORM\Mapping\JoinColumn')], \true),
        new NestedAnnotationToAttribute('Doctrine\ORM\Mapping\Table', [new AnnotationPropertyToAttributeClass('Doctrine\ORM\Mapping\Index', 'indexes', \true), new AnnotationPropertyToAttributeClass('Doctrine\ORM\Mapping\UniqueConstraint', 'uniqueConstraints', \true)]),
    ], 'doctrine/orm', '>=2.9');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [
        // class
        new AnnotationToAttribute('Doctrine\ORM\Mapping\Entity', null, ['repositoryClass']),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\Column'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\UniqueConstraint'),
        // id
        new AnnotationToAttribute('Doctrine\ORM\Mapping\Id'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\GeneratedValue'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\SequenceGenerator'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\Index'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\CustomIdGenerator', null, ['class']),
        // relations
        new AnnotationToAttribute('Doctrine\ORM\Mapping\OneToOne', null, ['targetEntity']),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\OneToMany', null, ['targetEntity']),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\ManyToMany', null, ['targetEntity']),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\JoinTable'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\ManyToOne', null, ['targetEntity']),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\OrderBy'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\JoinColumn'),
        // embed
        new AnnotationToAttribute('Doctrine\ORM\Mapping\Embeddable'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\Embedded', null, ['class']),
        // inheritance
        new AnnotationToAttribute('Doctrine\ORM\Mapping\MappedSuperclass', null, ['repositoryClass']),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\InheritanceType'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\DiscriminatorColumn'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\DiscriminatorMap'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\Version'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\ChangeTrackingPolicy'),
        // events
        new AnnotationToAttribute('Doctrine\ORM\Mapping\HasLifecycleCallbacks'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\PostLoad'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\PostPersist'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\PostRemove'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\PostUpdate'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\PreFlush'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\PrePersist'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\PreRemove'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\PreUpdate'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\Cache'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\EntityListeners'),
        // Overrides
        new AnnotationToAttribute('Doctrine\ORM\Mapping\AssociationOverrides'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\AssociationOverride'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\AttributeOverrides'),
        new AnnotationToAttribute('Doctrine\ORM\Mapping\AttributeOverride'),
    ], 'doctrine/orm', '>=2.9');
    // doctrine/mongodb-odm 2.3, the first version to ship mapping classes as PHP 8 attributes
    $rectorConfig->ruleWithConfigurationComposerVersionBound(NestedAnnotationToAttributeRector::class, [new NestedAnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Indexes', [new AnnotationPropertyToAttributeClass('Doctrine\ODM\MongoDB\Mapping\Annotations\Index'), new AnnotationPropertyToAttributeClass('Doctrine\ODM\MongoDB\Mapping\Annotations\UniqueIndex')], \true)], 'doctrine/mongodb-odm', '>=2.3');
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\ChunkSize'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\Filename'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\Length'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\Metadata'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File\UploadDate'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\AlsoLoad'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ChangeTrackingPolicy'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\DefaultDiscriminatorValue'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\DiscriminatorField'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\DiscriminatorMap'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\DiscriminatorValue'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Document'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Field'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\File'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\HasLifecycleCallbacks'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Id'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Index'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\InheritanceType'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Lock'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\MappedSuperclass'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PostLoad'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PostPersist'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PostRemove'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PostUpdate'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PreFlush'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PreLoad'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PrePersist'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PreRemove'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\PreUpdate'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\QueryResultDocument'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ReadPreference'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\ShardKey'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\UniqueIndex'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Validation'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\Version'), new AnnotationToAttribute('Doctrine\ODM\MongoDB\Mapping\Annotations\View')], 'doctrine/mongodb-odm', '>=2.3');
    // doctrine/mongodb-odm-bundle 4.4, the first version to ship the constraint as PHP 8 attribute
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique')], 'doctrine/mongodb-odm-bundle', '>=4.4');
    // gedmo/doctrine-extensions 3.5, the first version to ship mapping classes as PHP 8 attributes
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Gedmo\Mapping\Annotation\Blameable'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\IpTraceable'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Language'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Locale'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Loggable'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Reference'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\ReferenceIntegrity'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\ReferenceMany'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\ReferenceManyEmbed'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\ReferenceOne'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Slug'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\SlugHandler'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\SlugHandlerOption'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\SoftDeleteable'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\SortableGroup'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\SortablePosition'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Timestampable'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Translatable'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TranslationEntity'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Tree'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreeClosure'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreeLeft'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreeLevel'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreeLockTime'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreeParent'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreePath'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreePathHash'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreePathSource'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreeRight'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\TreeRoot'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Uploadable'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\UploadableFileMimeType'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\UploadableFileName'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\UploadableFilePath'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\UploadableFileSize'), new AnnotationToAttribute('Gedmo\Mapping\Annotation\Versioned')], 'gedmo/doctrine-extensions', '>=3.5');
};
