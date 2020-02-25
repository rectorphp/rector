<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareMethodTagValueNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareMethodTagValueNodeFactory implements AttributeNodeAwareFactoryInterface, AttributeAwareNodeFactoryAwareInterface
{
    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    public function getOriginalNodeClass(): string
    {
        return MethodTagValueNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, MethodTagValueNode::class, true);
    }

    /**
     * @param MethodTagValueNode $node
     */
    public function create(Node $node): AttributeAwareNodeInterface
    {
        $returnType = $this->createAttributeAwareReturnType($node);

        return new AttributeAwareMethodTagValueNode(
            $node->isStatic,
            $returnType,
            $node->methodName,
            $node->parameters,
            $node->description
        );
    }

    public function setAttributeAwareNodeFactory(AttributeAwareNodeFactory $attributeAwareNodeFactory): void
    {
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
    }

    /**
     * @return TypeNode&AttributeAwareNodeInterface
     */
    private function createAttributeAwareReturnType(MethodTagValueNode $methodTagValueNode)
    {
        if ($methodTagValueNode->returnType !== null) {
            return $this->attributeAwareNodeFactory->createFromNode($methodTagValueNode->returnType);
        }

        return $methodTagValueNode->returnType;
    }
}
