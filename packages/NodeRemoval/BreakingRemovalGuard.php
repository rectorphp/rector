<?php

declare (strict_types=1);
namespace Rector\NodeRemoval;

use PhpParser\Node;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class BreakingRemovalGuard
{
    /**
     * @deprecated
     */
    public function ensureNodeCanBeRemove(Node $node) : void
    {
        if ($this->isLegalNodeRemoval($node)) {
            return;
        }
        /** @var string $childOfNodeType */
        $childOfNodeType = $node->getAttribute(AttributeKey::CHILD_OF_NODE_TYPE);
        throw new ShouldNotHappenException(\sprintf('Node "%s" on line %d is child of "%s", so it cannot be removed as it would break PHP code. Change or remove the parent node instead.', \get_class($node), $node->getLine(), $childOfNodeType));
    }
    /**
     * @api
     */
    public function isLegalNodeRemoval(Node $node) : bool
    {
        return $node->getAttribute(AttributeKey::IS_BREAKING_REMOVAL_NODE) !== \true;
    }
}
