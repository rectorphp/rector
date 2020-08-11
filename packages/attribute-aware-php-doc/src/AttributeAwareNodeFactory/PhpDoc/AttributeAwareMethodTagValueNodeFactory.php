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
    public function create(Node $node, string $docContent): AttributeAwareNodeInterface
    {
        $returnType = $this->attributizeReturnType($node, $docContent);

        foreach ($node->parameters as $key => $parameter) {
            $node->parameters[$key] = $this->attributeAwareNodeFactory->createFromNode($parameter, $docContent);
        }

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

    private function attributizeReturnType(
        MethodTagValueNode $methodTagValueNode,
        string $docContent
    ): ?AttributeAwareNodeInterface {
        if ($methodTagValueNode->returnType !== null) {
            return $this->createAttributeAwareReturnType($methodTagValueNode->returnType, $docContent);
        }

        return null;
    }

    private function createAttributeAwareReturnType(TypeNode $typeNode, string $docContent): AttributeAwareNodeInterface
    {
        return $this->attributeAwareNodeFactory->createFromNode($typeNode, $docContent);
    }
}
