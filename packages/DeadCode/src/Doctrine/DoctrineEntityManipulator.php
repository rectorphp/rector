<?php

declare(strict_types=1);

namespace Rector\DeadCode\Doctrine;

use Doctrine\ORM\Mapping\Entity;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\InversedByNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\MappedByNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\InheritanceTypeTagValueNode;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class DoctrineEntityManipulator
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        NameResolver $nameResolver,
        DocBlockManipulator $docBlockManipulator,
        DoctrineDocBlockResolver $doctrineDocBlockResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->nameResolver = $nameResolver;
        $this->docBlockManipulator = $docBlockManipulator;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function resolveOtherProperty(Property $property): ?string
    {
        if ($property->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($property);

        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if ($relationTagValueNode === null) {
            return null;
        }

        $otherProperty = null;
        if ($relationTagValueNode instanceof MappedByNodeInterface) {
            $otherProperty = $relationTagValueNode->getMappedBy();
        }

        if ($otherProperty !== null) {
            return $otherProperty;
        }

        if ($relationTagValueNode instanceof InversedByNodeInterface) {
            return $relationTagValueNode->getInversedBy();
        }

        return null;
    }

    public function isNonAbstractDoctrineEntityClass(Class_ $class): bool
    {
        if ($class->isAnonymous()) {
            return false;
        }

        if ($class->isAbstract()) {
            return false;
        }

        // is parent entity
        if ($this->docBlockManipulator->hasTag($class, InheritanceTypeTagValueNode::class)) {
            return false;
        }

        return $this->docBlockManipulator->hasTag($class, EntityTagValueNode::class);
    }

    public function removeMappedByOrInversedByFromProperty(Property $property): void
    {
        $doc = $property->getDocComment();
        if ($doc === null) {
            return;
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($property);
        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);

        $shouldUpdate = false;
        if ($relationTagValueNode instanceof MappedByNodeInterface) {
            if ($relationTagValueNode->getMappedBy()) {
                $shouldUpdate = true;
                $relationTagValueNode->removeMappedBy();
            }
        }

        if ($relationTagValueNode instanceof InversedByNodeInterface) {
            if ($relationTagValueNode->getInversedBy()) {
                $shouldUpdate = true;
                $relationTagValueNode->removeInversedBy();
            }
        }

        if (! $shouldUpdate) {
            return;
        }

        $this->docBlockManipulator->updateNodeWithPhpDocInfo($property, $phpDocInfo);
    }

    /**
     * @return string[]
     */
    public function resolveRelationPropertyNames(Class_ $class): array
    {
        $manyToOnePropertyNames = [];

        foreach ($class->getProperties() as $property) {
            if ($property->getDocComment() === null) {
                continue;
            }

            $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($property);
            if (! $phpDocInfo->hasByType(DoctrineRelationTagValueNodeInterface::class)) {
                continue;
            }

            $manyToOnePropertyNames[] = $this->nameResolver->getName($property);
        }

        return $manyToOnePropertyNames;
    }

    public function isMethodCallOnDoctrineEntity(Node $node, string $methodName): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $this->nameResolver->isName($node->name, $methodName)) {
            return false;
        }

        $objectType = $this->nodeTypeResolver->getObjectType($node->var);
        if (! $objectType instanceof ObjectType) {
            return false;
        }

        return $this->doctrineDocBlockResolver->isDoctrineEntityClass($objectType->getClassName());
    }
}
