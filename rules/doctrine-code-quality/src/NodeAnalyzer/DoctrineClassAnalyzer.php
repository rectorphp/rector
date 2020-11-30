<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class DoctrineClassAnalyzer
{
    public function matchDoctrineEntityTagValueNode(Class_ $class): ?EntityTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $class->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(EntityTagValueNode::class);
    }
}
