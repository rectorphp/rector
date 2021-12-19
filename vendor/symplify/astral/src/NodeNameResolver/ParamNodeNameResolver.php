<?php

declare (strict_types=1);
namespace RectorPrefix20211219\Symplify\Astral\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use RectorPrefix20211219\Symplify\Astral\Contract\NodeNameResolverInterface;
final class ParamNodeNameResolver implements \RectorPrefix20211219\Symplify\Astral\Contract\NodeNameResolverInterface
{
    public function match(\PhpParser\Node $node) : bool
    {
        return $node instanceof \PhpParser\Node\Param;
    }
    /**
     * @param Param $node
     */
    public function resolve(\PhpParser\Node $node) : ?string
    {
        $paramName = $node->var->name;
        if ($paramName instanceof \PhpParser\Node\Expr) {
            return null;
        }
        return $paramName;
    }
}
