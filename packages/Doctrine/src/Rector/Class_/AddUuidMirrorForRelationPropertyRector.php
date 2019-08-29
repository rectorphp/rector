<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\VarLikeIdentifier;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Doctrine\Collector\EntityWithAddedPropertyCollector;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\ToManyTagNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\ToOneTagNodeInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
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
     * @var EntityWithAddedPropertyCollector
     */
    private $entityWithAddedPropertyCollector;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        PhpDocTagNodeFactory $phpDocTagNodeFactory,
        EntityWithAddedPropertyCollector $entityWithAddedPropertyCollector
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
        $this->entityWithAddedPropertyCollector = $entityWithAddedPropertyCollector;
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
        foreach ($node->stmts as $classStmt) {
            if (! $classStmt instanceof Property) {
                continue;
            }

            if ($this->shouldSkipProperty($node, $classStmt)) {
                continue;
            }

            $node->stmts[] = $this->createMirrorNullable($classStmt);
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
        $doctrineJoinColumnTagValueNode = $propertyPhpDocInfo->getDoctrineJoinColumnTagValueNode();

        if ($doctrineJoinColumnTagValueNode) {
            // replace @ORM\JoinColumn with @ORM\JoinTable
            $propertyPhpDocInfo->removeTagValueNodeFromNode($doctrineJoinColumnTagValueNode);
        }

        $propertyPhpDocInfo->getPhpDocNode()->children[] = $this->phpDocTagNodeFactory->createJoinTableTagNode(
            $property
        );
    }

    private function refactorToOnePropertyPhpDocInfo(PhpDocInfo $propertyPhpDocInfo): void
    {
        $joinColumnTagValueNode = $propertyPhpDocInfo->getDoctrineJoinColumnTagValueNode();

        if ($joinColumnTagValueNode) {
            $joinColumnTagValueNode->changeNullable(true);
            $joinColumnTagValueNode->changeReferencedColumnName('uuid');
        } else {
            $propertyPhpDocInfo->getPhpDocNode()->children[] = $this->phpDocTagNodeFactory->createJoinColumnTagNode();
        }
    }

    private function hasClassPropertyName(Class_ $node, string $uuidPropertyName): bool
    {
        foreach ($node->stmts as $stmt) {
            if (! $stmt instanceof Property) {
                continue;
            }

            if (! $this->isName($stmt, $uuidPropertyName)) {
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

        // the remote property has to have $uuid property, from @see \Rector\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector
        if (! property_exists($targetEntity, 'uuid')) {
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

        if ($doctrineRelationTagValueNode instanceof ToManyTagNodeInterface) {
            $this->entityWithAddedPropertyCollector->addClassToManyRelationProperty($className, $propertyName);
        } elseif ($doctrineRelationTagValueNode instanceof ToOneTagNodeInterface) {
            $this->entityWithAddedPropertyCollector->addClassToOneRelationProperty($className, $propertyName);
        }
    }
}
