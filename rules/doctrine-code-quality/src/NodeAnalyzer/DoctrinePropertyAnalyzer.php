<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class DoctrinePropertyAnalyzer
{
    public function matchDoctrineColumnTagValueNode(Property $property): ?ColumnTagValueNode
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

        return $phpDocInfo->getByType(ColumnTagValueNode::class);
    }

    public function matchDoctrineOneToManyTagValueNode(Property $property): ?OneToManyTagValueNode
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

        return $phpDocInfo->getByType(OneToManyTagValueNode::class);
    }

    public function matchDoctrineManyToOneTagValueNode(Property $property): ?ManyToOneTagValueNode
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        return $phpDocInfo->getByType(ManyToOneTagValueNode::class);
    }

    public function matchDoctrineJoinColumnTagValueNode(Property $property): ?JoinColumnTagValueNode
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        return $phpDocInfo->getByType(JoinColumnTagValueNode::class);
    }

    public function matchDoctrineGeneratedValueTagValueNode(Property $property): ?GeneratedValueTagValueNode
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        return $phpDocInfo->getByType(GeneratedValueTagValueNode::class);
    }
}
