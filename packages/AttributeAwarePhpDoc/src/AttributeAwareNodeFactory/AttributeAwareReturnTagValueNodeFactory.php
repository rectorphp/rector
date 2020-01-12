<?php

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory;

final class AttributeAwareReturnTagValueNodeFactory implements \Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode::class;
    }
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $node): bool
    {
        return is_a($node, \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode::class, true);
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode
     */
    public function create(\PHPStan\PhpDocParser\Ast\Node $node): \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
    {
        return new \Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareReturnTagValueNode($node->type, $node->description);
    }
}