<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareMethodTagValueParameterNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareMethodTagValueParameterNodeFactory implements AttributeNodeAwareFactoryInterface, AttributeAwareNodeFactoryAwareInterface
{
    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    public function getOriginalNodeClass(): string
    {
        return MethodTagValueParameterNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, MethodTagValueParameterNode::class, true);
    }

    /**
     * @param MethodTagValueParameterNode $node
     */
    public function create(Node $node, string $docContent): AttributeAwareNodeInterface
    {
        if ($node->type !== null) {
            $node->type = $this->attributeAwareNodeFactory->createFromNode($node->type, $docContent);
        }

        return new AttributeAwareMethodTagValueParameterNode(
            $node->type,
            $node->isReference,
            $node->isVariadic,
            $node->parameterName,
            $node->defaultValue
        );
    }

    public function setAttributeAwareNodeFactory(AttributeAwareNodeFactory $attributeAwareNodeFactory): void
    {
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
    }
}
