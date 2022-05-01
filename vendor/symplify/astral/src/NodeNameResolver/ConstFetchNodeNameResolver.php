<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\Astral\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220501\Symplify\Astral\Contract\NodeNameResolverInterface;
final class ConstFetchNodeNameResolver implements \RectorPrefix20220501\Symplify\Astral\Contract\NodeNameResolverInterface
{
    public function match(\PhpParser\Node $node) : bool
    {
        return $node instanceof \PhpParser\Node\Expr\ConstFetch;
    }
    /**
     * @param ConstFetch $node
     */
    public function resolve(\PhpParser\Node $node) : ?string
    {
        return $node->name->toString();
    }
}
