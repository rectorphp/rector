<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use RectorPrefix202208\Symplify\Astral\Contract\NodeNameResolverInterface;
final class IdentifierNodeNameResolver implements NodeNameResolverInterface
{
    public function match(Node $node) : bool
    {
        if ($node instanceof Identifier) {
            return \true;
        }
        return $node instanceof Name;
    }
    /**
     * @param Identifier|Name $node
     */
    public function resolve(Node $node) : ?string
    {
        return (string) $node;
    }
}
