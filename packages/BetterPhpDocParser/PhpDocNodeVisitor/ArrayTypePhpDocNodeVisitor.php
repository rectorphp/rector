<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class ArrayTypePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var AttributeMirrorer
     */
    private $attributeMirrorer;

    public function __construct(AttributeMirrorer $attributeMirrorer)
    {
        $this->attributeMirrorer = $attributeMirrorer;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof ArrayTypeNode) {
            return null;
        }

        if ($node instanceof SpacingAwareArrayTypeNode) {
            return null;
        }

        $spacingAwareArrayTypeNode = new SpacingAwareArrayTypeNode($node->type);
        $this->attributeMirrorer->mirror($node, $spacingAwareArrayTypeNode);

        return $spacingAwareArrayTypeNode;
    }
}
