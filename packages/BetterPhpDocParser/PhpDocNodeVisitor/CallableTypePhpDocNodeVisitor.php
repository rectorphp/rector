<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class CallableTypePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof CallableTypeNode) {
            return null;
        }

        if ($node instanceof SpacingAwareCallableTypeNode) {
            return null;
        }

        $spacingAwareCallableTypeNode = new SpacingAwareCallableTypeNode(
            $node->identifier,
            $node->parameters,
            $node->returnType
        );

        $parent = $node->getAttribute(PhpDocAttributeKey::PARENT);
        $spacingAwareCallableTypeNode->setAttribute(PhpDocAttributeKey::PARENT, $parent);

        return $spacingAwareCallableTypeNode;
    }
}
