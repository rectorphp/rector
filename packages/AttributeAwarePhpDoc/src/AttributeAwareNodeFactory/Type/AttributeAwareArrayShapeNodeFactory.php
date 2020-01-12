<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayShapeNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareArrayShapeNodeFactory implements AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return ArrayShapeNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, ArrayShapeNode::class, true);
    }

    /**
     * @param ArrayShapeNode $node
     */
    public function create(Node $node): AttributeAwareNodeInterface
    {
        return new AttributeAwareArrayShapeNode($node->items);
    }
}
