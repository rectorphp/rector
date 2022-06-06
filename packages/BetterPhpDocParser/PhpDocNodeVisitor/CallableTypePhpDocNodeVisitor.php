<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class CallableTypePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
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
        if (!$node instanceof CallableTypeNode) {
            return null;
        }
        if ($node instanceof SpacingAwareCallableTypeNode) {
            return null;
        }
        $spacingAwareCallableTypeNode = new SpacingAwareCallableTypeNode($node->identifier, $node->parameters, $node->returnType);
        $this->attributeMirrorer->mirror($node, $spacingAwareCallableTypeNode);
        return $spacingAwareCallableTypeNode;
    }
}
