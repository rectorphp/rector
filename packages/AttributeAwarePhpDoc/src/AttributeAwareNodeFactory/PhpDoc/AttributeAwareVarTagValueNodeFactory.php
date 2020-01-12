<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareVarTagValueNodeFactory implements AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return VarTagValueNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, VarTagValueNode::class, true);
    }

    /**
     * @param VarTagValueNode $node
     */
    public function create(Node $node): AttributeAwareNodeInterface
    {
        return new AttributeAwareVarTagValueNode($node->type, $node->variableName, $node->description);
    }
}
