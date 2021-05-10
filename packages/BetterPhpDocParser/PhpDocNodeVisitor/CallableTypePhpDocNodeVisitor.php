<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class CallableTypePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
{
    public function __construct(
        private AttributeMirrorer $attributeMirrorer
    ) {
    }

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

        $this->attributeMirrorer->mirror($node, $spacingAwareCallableTypeNode);

        return $spacingAwareCallableTypeNode;
    }
}
