<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use RectorPrefix20220418\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class CallableTypePhpDocNodeVisitor extends \RectorPrefix20220418\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor implements \Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Attributes\AttributeMirrorer
     */
    private $attributeMirrorer;
    public function __construct(\Rector\BetterPhpDocParser\Attributes\AttributeMirrorer $attributeMirrorer)
    {
        $this->attributeMirrorer = $attributeMirrorer;
    }
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node) : ?\PHPStan\PhpDocParser\Ast\Node
    {
        if (!$node instanceof \PHPStan\PhpDocParser\Ast\Type\CallableTypeNode) {
            return null;
        }
        if ($node instanceof \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode) {
            return null;
        }
        $spacingAwareCallableTypeNode = new \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode($node->identifier, $node->parameters, $node->returnType);
        $this->attributeMirrorer->mirror($node, $spacingAwareCallableTypeNode);
        return $spacingAwareCallableTypeNode;
    }
}
