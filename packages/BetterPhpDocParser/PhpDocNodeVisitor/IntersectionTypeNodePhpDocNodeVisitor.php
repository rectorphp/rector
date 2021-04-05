<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class IntersectionTypeNodePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof IntersectionTypeNode) {
            return null;
        }

        if ($node instanceof BracketsAwareIntersectionTypeNode) {
            return null;
        }

        $bracketsAwareUnionTypeNode = new BracketsAwareIntersectionTypeNode($node->types);

        $parent = $node->getAttribute(PhpDocAttributeKey::PARENT);
        $bracketsAwareUnionTypeNode->setAttribute(PhpDocAttributeKey::PARENT, $parent);

        return $bracketsAwareUnionTypeNode;
    }
}
