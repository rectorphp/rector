<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
use RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class ParamPhpDocNodeVisitor extends \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor implements \Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface
{
    /**
     * @var \Rector\BetterPhpDocParser\Attributes\AttributeMirrorer
     */
    private $attributeMirrorer;
    public function __construct(\Rector\BetterPhpDocParser\Attributes\AttributeMirrorer $attributeMirrorer)
    {
        $this->attributeMirrorer = $attributeMirrorer;
    }
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node) : ?\PHPStan\PhpDocParser\Ast\Node
    {
        if (!$node instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode) {
            return null;
        }
        if ($node instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode) {
            return null;
        }
        $variadicAwareParamTagValueNode = new \Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode($node->type, $node->isVariadic, $node->parameterName, $node->description);
        $this->attributeMirrorer->mirror($node, $variadicAwareParamTagValueNode);
        return $variadicAwareParamTagValueNode;
    }
}
