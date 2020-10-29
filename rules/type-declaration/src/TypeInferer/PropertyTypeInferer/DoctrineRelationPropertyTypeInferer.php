<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToOneTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class DoctrineRelationPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string
     */
    private const COLLECTION_TYPE = 'Doctrine\Common\Collections\Collection';

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    public function inferProperty(Property $property): Type
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return new MixedType();
        }

        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if ($relationTagValueNode === null) {
            return new MixedType();
        }

        if ($relationTagValueNode instanceof ToManyTagNodeInterface) {
            return $this->processToManyRelation($relationTagValueNode);
        }

        if ($relationTagValueNode instanceof ToOneTagNodeInterface) {
            $joinColumnTagValueNode = $phpDocInfo->getByType(JoinColumnTagValueNode::class);
            return $this->processToOneRelation($relationTagValueNode, $joinColumnTagValueNode);
        }

        return new MixedType();
    }

    public function getPriority(): int
    {
        return 2100;
    }

    private function processToManyRelation(ToManyTagNodeInterface $toManyTagNode): Type
    {
        $types = [];

        $targetEntity = $toManyTagNode->getTargetEntity();
        if ($targetEntity) {
            $types[] = new ArrayType(new MixedType(), new FullyQualifiedObjectType($targetEntity));
        }

        $types[] = new FullyQualifiedObjectType(self::COLLECTION_TYPE);

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    private function processToOneRelation(
        ToOneTagNodeInterface $toOneTagNode,
        ?JoinColumnTagValueNode $joinColumnTagValueNode
    ): Type {
        $types = [];

        $fullyQualifiedTargetEntity = $toOneTagNode->getFullyQualifiedTargetEntity();
        if ($fullyQualifiedTargetEntity) {
            $types[] = new FullyQualifiedObjectType($fullyQualifiedTargetEntity);
        }

        // nullable by default
        if ($joinColumnTagValueNode === null || $joinColumnTagValueNode->isNullable()) {
            $types[] = new NullType();
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
}
