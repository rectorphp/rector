<?php

declare (strict_types=1);
namespace Rector\NodeNestingScope;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ContextAnalyzer
{
    /**
     * @api
     */
    public function isInLoop(Node $node) : bool
    {
        return $node->getAttribute(AttributeKey::IS_IN_LOOP) === \true;
    }
    /**
     * @api
     */
    public function isInIf(Node $node) : bool
    {
        return $node->getAttribute(AttributeKey::IS_IN_IF) === \true;
    }
}
