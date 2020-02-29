<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeParameterNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareCallableTypeParameterNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareCallableTypeParameterNodeFactory implements AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return CallableTypeParameterNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, CallableTypeParameterNode::class, true);
    }

    /**
     * @param CallableTypeParameterNode $node
     */
    public function create(Node $node, string $docContent): AttributeAwareNodeInterface
    {
        return new AttributeAwareCallableTypeParameterNode(
            $node->type,
            $node->isReference,
            $node->isVariadic,
            $node->parameterName,
            $node->isOptional
        );
    }
}
