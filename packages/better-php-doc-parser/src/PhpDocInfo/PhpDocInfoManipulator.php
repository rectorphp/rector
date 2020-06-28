<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PhpDocInfoManipulator
{
    public function getPhpDocTagValueNode(Node $node, string $phpDocTagNodeClass): ?PhpDocTagValueNode
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType($phpDocTagNodeClass);
    }
}
