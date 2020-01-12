<?php

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory;

final class AttributeAwareUsesTagValueNodeFactory implements \Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return \PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode::class;
    }
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $node): bool
    {
        return is_a($node, \PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode::class, true);
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode
     */
    public function create(\PHPStan\PhpDocParser\Ast\Node $node): \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
    {
        return new \Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareUsesTagValueNode($node->type, $node->description);
    }
}