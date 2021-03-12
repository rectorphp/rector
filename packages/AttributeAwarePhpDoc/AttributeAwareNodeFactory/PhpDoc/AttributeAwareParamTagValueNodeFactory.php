<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareParamTagValueNodeFactory implements AttributeNodeAwareFactoryInterface, AttributeAwareNodeFactoryAwareInterface
{
    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    public function getOriginalNodeClass(): string
    {
        return ParamTagValueNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, ParamTagValueNode::class, true);
    }

    /**
     * @param ParamTagValueNode $node
     */
    public function create(Node $node, string $docContent): AttributeAwareNodeInterface
    {
        $node->type = $this->attributeAwareNodeFactory->createFromNode($node->type, $docContent);

        return new AttributeAwareParamTagValueNode(
            $node->type,
            $node->isVariadic,
            $node->parameterName,
            $node->description
        );
    }

    public function setAttributeAwareNodeFactory(AttributeAwareNodeFactory $attributeAwareNodeFactory): void
    {
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
    }
}
