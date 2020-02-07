<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayShapeItemNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareArrayShapeItemNodeFactory implements AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return ArrayShapeItemNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, ArrayShapeItemNode::class, true);
    }

    /**
     * @param ArrayShapeItemNode $node
     */
    public function create(Node $node): AttributeAwareNodeInterface
    {
        return new AttributeAwareArrayShapeItemNode($node->keyName, $node->optional, $node->valueType);
    }
}
