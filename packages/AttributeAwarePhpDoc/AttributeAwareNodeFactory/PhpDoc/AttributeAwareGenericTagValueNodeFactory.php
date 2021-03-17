<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareGenericTagValueNodeFactory implements AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return GenericTagValueNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, GenericTagValueNode::class, true);
    }

    /**
     * @param GenericTagValueNode $node
     */
    public function create(Node $node, string $docContent): AttributeAwareNodeInterface
    {
        return new AttributeAwareGenericTagValueNode($node->value);
    }
}
