<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\VarLikeIdentifier;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Doctrine\Collector\UuidMigrationDataCollector;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;
use Rector\Doctrine\Provider\EntityWithMissingUuidProvider;
use Rector\Doctrine\Uuid\JoinTableNameResolver;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\JoinColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\ToManyTagNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\ToOneTagNodeInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 * @see \Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\AddUuidMirrorForRelationPropertyRectorTest
 */
final class AddUuidMirrorForRelationPropertyRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var PhpDocTagNodeFactory
     */
    private $phpDocTagNodeFactory;

    /**
     * @var UuidMigrationDataCollector
     */
    private $uuidMigrationDataCollector;

    /**
     * @var JoinTableNameResolver
     */
    private $joinTableNameResolver;

    /**
     * @var EntityWithMissingUuidProvider
     */
    private $entityWithMissingUuidProvider;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        PhpDocTagNodeFactory $phpDocTagNodeFactory,
        UuidMigrationDataCollector $uuidMigrationDataCollector,
        JoinTableNameResolver $joinTableNameResolver,
        EntityWithMissingUuidProvider $entityWithMissingUuidProvider
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
        $this->uuidMigrationDataCollector = $uuidMigrationDataCollector;
        $this->joinTableNameResolver = $joinTableNameResolver;
        $this->entityWithMissingUuidProvider = $entityWithMissingUuidProvider;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds $uuid property to entities, that already have $id with integer type.' .
            'Require for step-by-step migration from int to uuid.'
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isDoctrineEntityClass($node)) {
            return null;
        }

        // traverse relations and see which of them have freshly added uuid on the other side
        foreach ($node->getProperties() as $property) {
            if ($this->shouldSkipProperty($node, $property)) {
                continue;
            }

            $node->stmts[] = $this->createMirrorNullable($property);
        }

        return $node;
    }

    /**
     * Creates duplicated property, that has "*uuidSuffix"
     * and nullable join column, so we cna complete them manually
     */
    private function createMirrorNullable(Property $property): Property
    {
        $propertyWithUuid = clone $property;

        // this is needed to keep old property name
        $this->updateDocComment($propertyWithUuid);

        // name must be changed after the doc comment update, because the reflection annotation needed for update of doc comment
        // would miss non existing *Uuid property
        $uuidPropertyName = $this->getName($propertyWithUuid) . 'Uuid';
        $newPropertyProperty = new PropertyProperty(new VarLikeIdentifier($uuidPropertyName));
        $propertyWithUuid->props = [$newPropertyProperty];

        $this->addNewPropertyToCollector($property, $uuidPropertyName);

        return $propertyWithUuid;
    }

    private function updateDocComment(Property $property): void
    {
        /** @var PhpDocInfo $propertyPhpDocInfo */
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);

        /** @var DoctrineRelationTagValueNodeInterface $doctrineRelationTagValueNode */
        $doctrineRelationTagValueNode = $this->getDoctrineRelationTagValueNode($property);

        if ($doctrineRelationTagValueNode instanceof ToManyTagNodeInterface) {
            $this->refactorToManyPropertyPhpDocInfo($propertyPhpDocInfo, $property);
        } elseif ($doctrineRelationTagValueNode instanceof ToOneTagNodeInterface) {
            $this->refactorToOnePropertyPhpDocInfo($propertyPhpDocInfo);
        }

        $this->docBlockManipulator->updateNodeWithPhpDocInfo($property, $propertyPhpDocInfo);
    }

    private function refactorToManyPropertyPhpDocInfo(PhpDocInfo $propertyPhpDocInfo, Property $property): void
    {
        $doctrineJoinColumnTagValueNode = $propertyPhpDocInfo->getByType(JoinColumnTagValueNode::class);
        if ($doctrineJoinColumnTagValueNode) {
            // replace @ORM\JoinColumn with @ORM\JoinTable
            $propertyPhpDocInfo->removeTagValueNodeFromNode($doctrineJoinColumnTagValueNode);
        }

        $joinTableTagNode = $this->phpDocTagNodeFactory->createJoinTableTagNode($property);
        $propertyPhpDocInfo->getPhpDocNode()->children[] = $joinTableTagNode;
    }

    private function refactorToOnePropertyPhpDocInfo(PhpDocInfo $propertyPhpDocInfo): void
    {
        $joinColumnTagValueNode = $propertyPhpDocInfo->getByType(JoinColumnTagValueNode::class);

        if ($joinColumnTagValueNode) {
            $joinColumnTagValueNode->changeNullable(true);
            $joinColumnTagValueNode->changeReferencedColumnName('uuid');
        } else {
            $propertyPhpDocInfo->getPhpDocNode()->children[] = $this->phpDocTagNodeFactory->createJoinColumnTagNode();
        }
    }

    private function hasClassPropertyName(Class_ $node, string $uuidPropertyName): bool
    {
        foreach ($node->getProperties() as $property) {
            if (! $this->isName($property, $uuidPropertyName)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function shouldSkipProperty(Class_ $class, Property $property): bool
    {
        // this relation already is or has uuid property
        if ($this->isName($property, '*Uuid')) {
            return true;
        }

        $uuidPropertyName = $this->getName($property) . 'Uuid';

        if ($this->hasClassPropertyName($class, $uuidPropertyName)) {
            return true;
        }

        $targetEntity = $this->getTargetEntity($property);
        if ($targetEntity === null) {
            return true;
        }

        if (! $this->isTargetClassEntityWithMissingUuid($targetEntity)) {
            return true;
        }

        return false;
    }

    private function addNewPropertyToCollector(Property $property, string $propertyName): void
    {
        /** @var string $className */
        $className = $property->getAttribute(AttributeKey::CLASS_NAME);

        /** @var DoctrineRelationTagValueNodeInterface $doctrineRelationTagValueNode */
        $doctrineRelationTagValueNode = $this->getDoctrineRelationTagValueNode($property);

        $currentJoinTableName = $this->joinTableNameResolver->resolveManyToManyTableNameForProperty($property);
        $uuidJoinTableName = $currentJoinTableName . '_uuid';

        if ($doctrineRelationTagValueNode instanceof ToManyTagNodeInterface) {
            $this->uuidMigrationDataCollector->addClassToManyRelationProperty(
                $className,
                $propertyName,
                $currentJoinTableName,
                $uuidJoinTableName
            );
        } elseif ($doctrineRelationTagValueNode instanceof ToOneTagNodeInterface) {
            $this->uuidMigrationDataCollector->addClassToOneRelationProperty($className, $propertyName);
        }
    }

    private function isTargetClassEntityWithMissingUuid(string $targetEntity): bool
    {
        foreach ($this->entityWithMissingUuidProvider->provide() as $entityWithMissingUuid) {
            if ($this->isName($entityWithMissingUuid, $targetEntity)) {
                return true;
            }
        }

        return false;
    }
}
