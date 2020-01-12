<?php

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory;

final class AttributeAwareInvalidTagValueNodeFactory implements \Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return \PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode::class;
    }
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $node): bool
    {
        return is_a($node, \PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode::class, true);
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode
     */
    public function create(\PHPStan\PhpDocParser\Ast\Node $node): \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
    {
        return new \Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareInvalidTagValueNode($node->value, $node->exception);
    }
}