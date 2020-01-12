<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

final class AttributeAwareThisTypeNodeFactory implements \Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return \PHPStan\PhpDocParser\Ast\Type\ThisTypeNode::class;
    }

    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $node): bool
    {
        return is_a($node, \PHPStan\PhpDocParser\Ast\Type\ThisTypeNode::class, true);
    }

    /**
     * @param \PHPStan\PhpDocParser\Ast\Type\ThisTypeNode $node
     */
    public function create(
        \PHPStan\PhpDocParser\Ast\Node $node
    ): \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface {
        return new \Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareThisTypeNode();
    }
}
