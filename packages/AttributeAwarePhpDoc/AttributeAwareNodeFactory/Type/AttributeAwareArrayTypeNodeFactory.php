<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayTypeNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\PhpDocNodeTransformerInterface;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;

final class AttributeAwareArrayTypeNodeFactory implements PhpDocNodeTransformerInterface, AttributeAwareNodeFactoryAwareInterface
{
    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    public function isMatch(Node $node): bool
    {
        return is_a($node, ArrayTypeNode::class, true);
    }

    /**
     * @param ArrayTypeNode $node
     * @return AttributeAwareArrayTypeNode
     */
    public function transform(Node $node, string $docContent): Node
    {
        $node->type = $this->attributeAwareNodeFactory->transform($node->type, $docContent);

        return new AttributeAwareArrayTypeNode($node->type);
    }

    public function setAttributeAwareNodeFactory(AttributeAwareNodeFactory $attributeAwareNodeFactory): void
    {
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
    }
}
