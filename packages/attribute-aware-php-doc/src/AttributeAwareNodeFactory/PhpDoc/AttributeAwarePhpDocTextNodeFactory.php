<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTextNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwarePhpDocTextNodeFactory implements AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return PhpDocTextNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, PhpDocTextNode::class, true);
    }

    /**
     * @param PhpDocTextNode $node
     */
    public function create(Node $node, string $docContent): AttributeAwareNodeInterface
    {
        return new AttributeAwarePhpDocTextNode($node->text);
    }
}
