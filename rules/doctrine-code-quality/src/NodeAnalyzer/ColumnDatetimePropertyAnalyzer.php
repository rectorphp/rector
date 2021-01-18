<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ColumnDatetimePropertyAnalyzer
{
    public function matchDateTimeColumnTagValueNodeInProperty(Property $property): ?ColumnTagValueNode
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

        $columnTagValueNode = $phpDocInfo->getByType(ColumnTagValueNode::class);
        if ($columnTagValueNode === null) {
            return null;
        }

        /** @var ColumnTagValueNode $columnTagValueNode */
        if ($columnTagValueNode->getType() !== 'datetime') {
            return null;
        }

        return $columnTagValueNode;
    }
}
