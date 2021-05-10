<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\Astral\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20210510\Symplify\Astral\Contract\NodeNameResolverInterface;
final class ConstFetchNodeNameResolver implements NodeNameResolverInterface
{
    public function match(Node $node) : bool
    {
        return $node instanceof ConstFetch;
    }
    /**
     * @param ConstFetch $node
     */
    public function resolve(Node $node) : ?string
    {
        // convention to save uppercase and lowercase functions for each name
        return $node->name->toLowerString();
    }
}
