<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\BracketsAwareIntersectionTypeNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\PhpDocNodeTransformerInterface;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;

final class AttributeAwareIntersectionTypeNodeFactory implements PhpDocNodeTransformerInterface, AttributeAwareNodeFactoryAwareInterface
{
    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    public function isMatch(Node $node): bool
    {
        return is_a($node, IntersectionTypeNode::class, true);
    }

    /**
     * @param IntersectionTypeNode $node
     */
    public function transform(Node $node, string $docContent): Node
    {
        foreach ($node->types as $key => $intersectionedType) {
            $node->types[$key] = $this->attributeAwareNodeFactory->transform($intersectionedType, $docContent);
        }

        return new BracketsAwareIntersectionTypeNode($node->types);
    }

    public function setAttributeAwareNodeFactory(AttributeAwareNodeFactory $attributeAwareNodeFactory): void
    {
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
    }
}
