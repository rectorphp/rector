<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ColumnDatetimePropertyAnalyzer
{
    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    public function __construct(DoctrineDocBlockResolver $doctrineDocBlockResolver)
    {
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
    }

    public function matchDateTimeColumnTagValueNodeInProperty(Property $property): ?ColumnTagValueNode
    {
        if (! $this->doctrineDocBlockResolver->isDoctrineProperty($property)) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
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
