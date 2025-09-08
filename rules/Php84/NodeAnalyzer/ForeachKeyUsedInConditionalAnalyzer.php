<?php

declare (strict_types=1);
namespace Rector\Php84\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class ForeachKeyUsedInConditionalAnalyzer
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isUsed(Variable $variable, Expr $expr): bool
    {
        $keyVarName = (string) $this->nodeNameResolver->getName($variable);
        return (bool) $this->betterNodeFinder->findVariableOfName($expr, $keyVarName);
    }
}
