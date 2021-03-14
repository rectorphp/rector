<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIntersectionTypeNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;

final class AttributeAwareIntersectionTypeNodeFactory implements AttributeNodeAwareFactoryInterface, AttributeAwareNodeFactoryAwareInterface
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
    public function create(Node $node, string $docContent): AttributeAwareIntersectionTypeNode
    {
        foreach ($node->types as $key => $intersectionedType) {
            $node->types[$key] = $this->attributeAwareNodeFactory->createFromNode($intersectionedType, $docContent);
        }

        return new AttributeAwareIntersectionTypeNode($node->types);
    }

    public function setAttributeAwareNodeFactory(AttributeAwareNodeFactory $attributeAwareNodeFactory): void
    {
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
    }
}
