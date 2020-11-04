<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class DoctrinePropertyAnalyzer
{
    public function matchDoctrineColumnTagValueNode(Property $property): ?ColumnTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(ColumnTagValueNode::class);
    }

    public function matchDoctrineOneToManyTagValueNode(Property $property): ?OneToManyTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(OneToManyTagValueNode::class);
    }

    public function matchDoctrineOneToOneTagValueNode(Property $property): ?OneToOneTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(OneToOneTagValueNode::class);
    }

    public function matchDoctrineManyToManyTagValueNode(Property $property): ?ManyToManyTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(ManyToManyTagValueNode::class);
    }

    public function matchDoctrineManyToOneTagValueNode(Property $property): ?ManyToOneTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(ManyToOneTagValueNode::class);
    }

    public function matchDoctrineJoinColumnTagValueNode(Property $property): ?JoinColumnTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(JoinColumnTagValueNode::class);
    }

    public function matchDoctrineGeneratedValueTagValueNode(Property $property): ?GeneratedValueTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(GeneratedValueTagValueNode::class);
    }
}
