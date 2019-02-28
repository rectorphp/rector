<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\NodeDecorator;

use PhpParser\Node as PhpParserNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\Ast\NodeTraverser;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeDecoratorInterface;

final class StringsTypePhpDocNodeDecorator implements PhpDocNodeDecoratorInterface
{
    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(NodeTraverser $nodeTraverser)
    {
        $this->nodeTraverser = $nodeTraverser;
    }

    public function decorate(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode,
        PhpParserNode $phpParserNode
    ): AttributeAwarePhpDocNode {
        $this->nodeTraverser->traverseWithCallable(
            $attributeAwarePhpDocNode,
            function (AttributeAwareNodeInterface $phpParserNode) {
                $typeNode = $this->resolveTypeNode($phpParserNode);
                if ($typeNode === null) {
                    return $phpParserNode;
                }

                $typeAsString = (string) $typeNode;
                $typeAsArray = explode('|', $typeAsString);

                $phpParserNode->setAttribute(Attribute::TYPE_AS_ARRAY, $typeAsArray);
                $phpParserNode->setAttribute(Attribute::TYPE_AS_STRING, $typeAsString);

                return $phpParserNode;
            }
        );

        return $attributeAwarePhpDocNode;
    }

    private function resolveTypeNode(Node $node): ?TypeNode
    {
        if ($node instanceof ParamTagValueNode || $node instanceof VarTagValueNode || $node instanceof ReturnTagValueNode) {
            return $node->type;
        }

        if ($node instanceof TypeNode) {
            return $node;
        }

        return null;
    }
}
