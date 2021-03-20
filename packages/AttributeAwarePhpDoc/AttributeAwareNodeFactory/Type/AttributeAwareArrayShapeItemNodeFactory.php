<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\SpacingAwareArrayShapeItemNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\PhpDocNodeTransformerInterface;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;

final class AttributeAwareArrayShapeItemNodeFactory implements PhpDocNodeTransformerInterface, AttributeAwareNodeFactoryAwareInterface
{
    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    public function isMatch(Node $node): bool
    {
        return is_a($node, ArrayShapeItemNode::class, true);
    }

    /**
     * @param ArrayShapeItemNode $node
     * @return SpacingAwareArrayShapeItemNode
     */
    public function transform(Node $node, string $docContent): Node
    {
        $node->valueType = $this->attributeAwareNodeFactory->transform($node->valueType, $docContent);
        return new SpacingAwareArrayShapeItemNode($node->keyName, $node->optional, $node->valueType, $docContent);
    }

    public function setAttributeAwareNodeFactory(AttributeAwareNodeFactory $attributeAwareNodeFactory): void
    {
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
    }
}
