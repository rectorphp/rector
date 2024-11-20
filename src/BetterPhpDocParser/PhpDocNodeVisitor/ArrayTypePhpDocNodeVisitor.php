<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class ArrayTypePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
{
    /**
     * @readonly
     */
    private AttributeMirrorer $attributeMirrorer;
    public function __construct(AttributeMirrorer $attributeMirrorer)
    {
        $this->attributeMirrorer = $attributeMirrorer;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof ArrayTypeNode) {
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
