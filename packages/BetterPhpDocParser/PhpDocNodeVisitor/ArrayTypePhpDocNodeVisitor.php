<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class ArrayTypePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Attributes\AttributeMirrorer
     */
    private $attributeMirrorer;
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
