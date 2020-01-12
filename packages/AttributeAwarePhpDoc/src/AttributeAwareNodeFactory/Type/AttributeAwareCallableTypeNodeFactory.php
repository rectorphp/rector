<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

final class AttributeAwareCallableTypeNodeFactory implements \Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return \PHPStan\PhpDocParser\Ast\Type\CallableTypeNode::class;
    }

    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $node): bool
    {
        return is_a($node, \PHPStan\PhpDocParser\Ast\Type\CallableTypeNode::class, true);
    }

    /**
     * @param \PHPStan\PhpDocParser\Ast\Type\CallableTypeNode $node
     */
    public function create(
        \PHPStan\PhpDocParser\Ast\Node $node
    ): \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface {
        return new \Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareCallableTypeNode(
            $node->identifier,
            $node->parameters,
            $node->returnType
        );
    }
}
