<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareCallableTypeNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareCallableTypeNodeFactory implements AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return CallableTypeNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, CallableTypeNode::class, true);
    }

    /**
     * @param CallableTypeNode $node
     */
    public function create(Node $node): AttributeAwareNodeInterface
    {
        return new AttributeAwareCallableTypeNode($node->identifier, $node->parameters, $node->returnType);
    }
}
