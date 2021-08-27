<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\Astral\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use RectorPrefix20210827\Symplify\Astral\Contract\NodeNameResolverInterface;
final class FuncCallNodeNameResolver implements \RectorPrefix20210827\Symplify\Astral\Contract\NodeNameResolverInterface
{
    /**
     * @param \PhpParser\Node $node
     */
    public function match($node) : bool
    {
        return $node instanceof \PhpParser\Node\Expr\FuncCall;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : ?string
    {
        if ($node->name instanceof \PhpParser\Node\Expr) {
            return null;
        }
        return (string) $node->name;
    }
}
