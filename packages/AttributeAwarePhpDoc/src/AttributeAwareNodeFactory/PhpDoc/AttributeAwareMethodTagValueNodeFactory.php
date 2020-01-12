<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareMethodTagValueNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareMethodTagValueNodeFactory implements AttributeNodeAwareFactoryInterface
{
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
        return new AttributeAwareMethodTagValueNode(
            $node->isStatic,
            $node->returnType,
            $node->methodName,
            $node->parameters,
            $node->description
        );
    }
}
