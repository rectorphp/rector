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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\InheritanceTypeTagValueNode;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class DoctrineEntityManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        DoctrineDocBlockResolver $doctrineDocBlockResolver,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function resolveOtherProperty(Property $property): ?string
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

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

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $class->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        // is parent entity
        if ($phpDocInfo->hasByType(InheritanceTypeTagValueNode::class)) {
            return false;
        }

        return $phpDocInfo->hasByType(EntityTagValueNode::class);
    }

    public function removeMappedByOrInversedByFromProperty(Property $property): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);

        $shouldUpdate = false;
        if ($relationTagValueNode instanceof MappedByNodeInterface && $relationTagValueNode->getMappedBy()) {
            $shouldUpdate = true;
            $relationTagValueNode->removeMappedBy();
        }

        if ($relationTagValueNode instanceof InversedByNodeInterface && $relationTagValueNode->getInversedBy()) {
            $shouldUpdate = true;
            $relationTagValueNode->removeInversedBy();
        }

        if (! $shouldUpdate) {
            return;
        }
    }

    public function isMethodCallOnDoctrineEntity(Node $node, string $methodName): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($node->name, $methodName)) {
            return false;
        }

        $objectType = $this->nodeTypeResolver->resolve($node->var);
        if (! $objectType instanceof ObjectType) {
            return false;
        }

        return $this->doctrineDocBlockResolver->isDoctrineEntityClass($objectType->getClassName());
    }
}
